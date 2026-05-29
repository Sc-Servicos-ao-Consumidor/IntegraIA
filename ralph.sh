#!/bin/bash
#
# ralph.sh
#
# Orquestrador que le docs/project-phases.md, quebra em fases,
# e alimenta cada uma ao Codex CLI ou Claude Code para implementacao automatica.
#
# Uso:
#   chmod +x ralph.sh
#   ./ralph.sh [--engine codex|claude] [--from-phase N] [--only-phase N] [caminho-do-arquivo]
#
# Exemplos:
#   ./ralph.sh
#   ./ralph.sh --engine codex
#   ./ralph.sh --engine claude docs/project-phases.md
#   ./ralph.sh --from-phase 4
#   ./ralph.sh --only-phase 4
#   TOKEN_WAIT_SECONDS=900 ./ralph.sh --from-phase 4
#
# Configuracoes opcionais:
#   TOKEN_WAIT_SECONDS=600 ./ralph.sh
#   TOKEN_WAIT_SECONDS=600 TOKEN_WAIT_MAX_ATTEMPTS=6 ./ralph.sh
#
# Pre-requisitos:
#   - Codex: npm install -g @openai/codex + OPENAI_API_KEY no ambiente
#   - Claude: npm install -g @anthropic-ai/claude-code + login feito no Claude Code CLI
#   - Estar na raiz do projeto Laravel, dentro de um repo git

set -euo pipefail

ENGINE="claude"
INPUT_FILE=""
FROM_PHASE=""
ONLY_PHASE=""

while [[ $# -gt 0 ]]; do
  case "$1" in
    --engine)
      ENGINE="$2"
      shift 2
      ;;
    --engine=*)
      ENGINE="${1#*=}"
      shift
      ;;
    --from-phase)
      FROM_PHASE="$2"
      shift 2
      ;;
    --from-phase=*)
      FROM_PHASE="${1#*=}"
      shift
      ;;
    --only-phase)
      ONLY_PHASE="$2"
      shift 2
      ;;
    --only-phase=*)
      ONLY_PHASE="${1#*=}"
      shift
      ;;
    *)
      INPUT_FILE="$1"
      shift
      ;;
  esac
done

INPUT_FILE="${INPUT_FILE:-docs/project-phases.md}"

if [[ "$ENGINE" != "codex" && "$ENGINE" != "claude" ]]; then
  echo "Engine invalida: $ENGINE. Use 'codex' ou 'claude'."
  exit 1
fi

if [[ -n "$FROM_PHASE" && ! "$FROM_PHASE" =~ ^[0-9]+$ ]]; then
  echo "--from-phase deve ser um numero inteiro."
  exit 1
fi

if [[ -n "$ONLY_PHASE" && ! "$ONLY_PHASE" =~ ^[0-9]+$ ]]; then
  echo "--only-phase deve ser um numero inteiro."
  exit 1
fi

if [[ -n "$FROM_PHASE" && -n "$ONLY_PHASE" ]]; then
  echo "Use apenas --from-phase ou --only-phase, nao os dois ao mesmo tempo."
  exit 1
fi

PHASES_DIR=".phases"
LOG_DIR=".phases/logs"
PROMPT_DIR=".phases/prompts"
MANIFEST="$PHASES_DIR/manifest.txt"

# Fica fora de .phases porque .phases e recriada a cada execucao.
PROGRESS_FILE=".ralph-progress"

MAX_RETRIES=2

# Quando detectar limite/token/rate limit, aguarda e tenta novamente.
# 600 = 10 minutos.
TOKEN_WAIT_SECONDS="${TOKEN_WAIT_SECONDS:-600}"

# 0 = espera infinitamente ate voltar.
# Exemplo: TOKEN_WAIT_MAX_ATTEMPTS=6 tenta por 1 hora se TOKEN_WAIT_SECONDS=600.
TOKEN_WAIT_MAX_ATTEMPTS="${TOKEN_WAIT_MAX_ATTEMPTS:-0}"

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

log()     { echo -e "${BLUE}[$(date '+%H:%M:%S')]${NC} $1"; }
success() { echo -e "${GREEN}[$(date '+%H:%M:%S')] $1${NC}"; }
warn()    { echo -e "${YELLOW}[$(date '+%H:%M:%S')] $1${NC}"; }
fail()    { echo -e "${RED}[$(date '+%H:%M:%S')] $1${NC}"; }

format_duration() {
  local total_seconds=$1
  local hours=$((total_seconds / 3600))
  local minutes=$(((total_seconds % 3600) / 60))
  local seconds=$((total_seconds % 60))

  if [ "$hours" -gt 0 ]; then
    printf "%dh %dm %ds" "$hours" "$minutes" "$seconds"
  elif [ "$minutes" -gt 0 ]; then
    printf "%dm %ds" "$minutes" "$seconds"
  else
    printf "%ds" "$seconds"
  fi
}

format_timestamp() {
  local ts=$1

  if [[ "$(uname)" == "Darwin" ]]; then
    date -r "$ts" '+%d/%m/%Y %H:%M:%S'
  else
    date -d "@$ts" '+%d/%m/%Y %H:%M:%S'
  fi
}

TOKEN_ERROR_PATTERNS=(
  "insufficient_quota"
  "exceeded your current quota"
  "rate_limit_exceeded"
  "rate limit"
  "rate limits"
  "usage limit"
  "usage_limit"
  "usage limits"
  "limit reached"
  "limit exceeded"
  "quota exceeded"
  "billing_hard_limit_reached"
  "credit balance is too low"
  "Your credit balance"
  "overloaded_error"
  "overloaded"
  "insufficient balance"
  "payment required"
  "HTTP 402"
  "HTTP 429"
  "status: 402"
  "status: 429"
  "529"
  "too many requests"
  "try again later"
  "please try again later"
  "temporarily unavailable"
)

has_token_exhaustion() {
  local log_file="$1"
  [ -f "$log_file" ] || return 1

  for pattern in "${TOKEN_ERROR_PATTERNS[@]}"; do
    if grep -qi "$pattern" "$log_file" 2>/dev/null; then
      echo ""
      warn "╔══════════════════════════════════════════════════════════╗"
      warn "║  LIMITE / RATE LIMIT / USAGE LIMIT DETECTADO            ║"
      warn "╠══════════════════════════════════════════════════════════╣"
      warn "║  Padrao detectado: $pattern"
      warn "║  Log: $log_file"
      warn "╚══════════════════════════════════════════════════════════╝"
      echo ""
      return 0
    fi
  done

  return 1
}

wait_for_token_recovery() {
  local wait_attempt="$1"

  if [[ "$TOKEN_WAIT_MAX_ATTEMPTS" -gt 0 && "$wait_attempt" -gt "$TOKEN_WAIT_MAX_ATTEMPTS" ]]; then
    fail "Limite de esperas atingido: $TOKEN_WAIT_MAX_ATTEMPTS"
    return 1
  fi

  warn "Aguardando $(format_duration "$TOKEN_WAIT_SECONDS") antes de tentar novamente..."
  warn "Tentativa de espera: $wait_attempt"

  sleep "$TOKEN_WAIT_SECONDS"

  return 0
}

preflight_checks() {
  if [[ "$ENGINE" == "codex" ]]; then
    if ! command -v codex &> /dev/null; then
      fail "codex CLI nao encontrado. Instale com: npm install -g @openai/codex"
      exit 1
    fi

    if [[ -z "${OPENAI_API_KEY:-}" ]]; then
      fail "OPENAI_API_KEY nao definida no ambiente."
      exit 1
    fi
  elif [[ "$ENGINE" == "claude" ]]; then
    if ! command -v claude &> /dev/null; then
      fail "Claude Code CLI nao encontrado. Instale com: npm install -g @anthropic-ai/claude-code"
      exit 1
    fi

    if [[ -z "${ANTHROPIC_API_KEY:-}" ]]; then
      warn "ANTHROPIC_API_KEY nao definida. Continuando com a autenticacao do Claude Code CLI."
    fi
  fi

  if [ ! -f "$INPUT_FILE" ]; then
    fail "Arquivo nao encontrado: $INPUT_FILE"
    exit 1
  fi

  if [ ! -f "artisan" ]; then
    warn "Nao parece ser a raiz de um projeto Laravel (artisan nao encontrado)"
    read -p "Continuar mesmo assim? (y/N) " -n 1 -r
    echo
    [[ $REPLY =~ ^[Yy]$ ]] || exit 1
  fi

  if ! git rev-parse --is-inside-work-tree &> /dev/null 2>&1; then
    fail "Requer um repositorio git."
    exit 1
  fi

  success "Pre-checks OK (engine: $ENGINE)"
}

split_phases() {
  log "Quebrando $INPUT_FILE em fases..."

  rm -rf "$PHASES_DIR"
  mkdir -p "$PHASES_DIR" "$LOG_DIR" "$PROMPT_DIR"
  > "$MANIFEST"

  local current_file=""
  local phase_count=0

  while IFS= read -r line || [ -n "$line" ]; do
    if [[ "$line" =~ ^##[[:space:]]+(Phase[[:space:]]+[0-9]+[^#]*) ]]; then
      phase_count=$((phase_count + 1))

      local raw_title="${BASH_REMATCH[1]}"
      raw_title="$(echo "$raw_title" | sed 's/[[:space:]]*$//')"

      local slug
      slug=$(echo "$raw_title" \
        | tr '[:upper:]' '[:lower:]' \
        | sed 's/phase[[:space:]]*/phase-/' \
        | sed 's/[^a-z0-9-]/-/g' \
        | sed 's/--*/-/g' \
        | sed 's/-$//' \
        | sed 's/^-//')

      slug=$(echo "$slug" \
        | sed -E 's/phase-([0-9])$/phase-0\1/' \
        | sed -E 's/phase-([0-9])-/phase-0\1-/')

      current_file="$PHASES_DIR/${slug}.md"

      echo "$line" > "$current_file"
      echo "${slug}.md|${raw_title}" >> "$MANIFEST"

      continue
    fi

    if [ -n "$current_file" ]; then
      echo "$line" >> "$current_file"
    fi
  done < "$INPUT_FILE"

  success "$phase_count fases extraidas"
}

phase_number_from_title() {
  local title="$1"

  echo "$title" | sed -E 's/^Phase[[:space:]]+([0-9]+).*/\1/'
}

should_skip_phase_by_filter() {
  local title="$1"
  local phase_number
  phase_number=$(phase_number_from_title "$title")

  if [[ -n "$FROM_PHASE" && "$phase_number" -lt "$FROM_PHASE" ]]; then
    return 0
  fi

  if [[ -n "$ONLY_PHASE" && "$phase_number" -ne "$ONLY_PHASE" ]]; then
    return 0
  fi

  return 1
}

skip_reason_for_phase() {
  local title="$1"
  local phase_number
  phase_number=$(phase_number_from_title "$title")

  if [[ -n "$FROM_PHASE" && "$phase_number" -lt "$FROM_PHASE" ]]; then
    echo "--from-phase $FROM_PHASE"
    return 0
  fi

  if [[ -n "$ONLY_PHASE" && "$phase_number" -ne "$ONLY_PHASE" ]]; then
    echo "--only-phase $ONLY_PHASE"
    return 0
  fi

  echo ""
}

build_prompt_file() {
  local phase_file="$1"
  local prompt_file="$PROMPT_DIR/${phase_file%.md}.txt"

  cat > "$prompt_file" <<PROMPT
Voce e um desenvolvedor Laravel senior implementando uma feature de busca semantica de catalogo de produtos.

## Stack do projeto
- Laravel v13, PHP 8.5
- Inertia.js v2 + Vue 3 (NAO usa Livewire — nunca crie nada relacionado a Livewire)
- Pest PHP v4 (testes — rode com: ./vendor/bin/sail artisan test --compact)
- Tailwind CSS v4
- PostgreSQL + pgvector (via Laravel Sail / Docker)
- Filas: Laravel Horizon + Redis
- XLS: maatwebsite/excel
- IA e embeddings: laravel/ai SDK (v0)
- Autenticacao de API: Laravel Sanctum

## Arquivos de referencia obrigatorios
- CLAUDE.md — regras do projeto (LEIA ANTES de qualquer coisa — siga todas as regras)
- docs/project-phases.md — plano completo de fases (contexto geral)
- docs/user-stories.md — user stories e criterios de aceite
- docs/database-schema.md — schema das tabelas do catalogo (catalog_products, catalog_packages, etc.)
- docs/project-description.md — descricao geral da plataforma

## Escopo desta feature
BACKEND ONLY. Nao crie:
- Views Blade, paginas Inertia, componentes Vue
- CSS, classes Tailwind, layouts
- Qualquer coisa relacionada a Livewire

## Convencoes obrigatorias (do CLAUDE.md)
- Use Sail para TODOS os comandos: ./vendor/bin/sail artisan ...
- NUNCA encadeie comandos de criacao de migration com && ou ; (timestamps ficam identicos)
- Registre Observers via atributo PHP no model, NAO em AppServiceProvider
- Services em app/Services/Catalog/, logica de negocio fora dos controllers
- Jobs em app/Jobs/Catalog/
- Controllers de API em app/Http/Controllers/Api/Catalog/
- Enums em app/Enums/
- Imports XLS em app/Imports/
- Crie factories para todos os models novos
- NAO use BelongsToTenant em models que sao acessados por jobs (sem sessao no CLI/queue)
- Tenant e resolvido por parametro explicito ou pelo token Sanctum do usuario autenticado

## Sua tarefa
Implemente COMPLETAMENTE cada item [ ] da fase descrita abaixo.

Para cada item:
1. Implemente o codigo completo — sem TODOs (exceto onde o plano explicita "stub")
2. Crie os testes Pest listados como criterio de aceite
3. Rode os testes: ./vendor/bin/sail artisan test --compact
4. Se um teste falhar, corrija o codigo e rode novamente
5. Quando os testes passarem, formate: ./vendor/bin/sail php vendor/bin/pint --dirty --format agent
6. So avance ao proximo item quando os testes estiverem verdes

Ao final de todos os itens, rode uma vez:
./vendor/bin/sail artisan test --compact

## Fase a implementar
$(cat "$PHASES_DIR/$phase_file")
PROMPT

  echo "$prompt_file"
}

build_retry_prompt_file() {
  local phase_file="$1"
  local test_output="$2"
  local prompt_file="$PROMPT_DIR/${phase_file%.md}-retry.txt"

  cat > "$prompt_file" <<PROMPT
Os testes falharam apos a implementacao anterior. Corrija os erros sem alterar o que ja estava funcionando.

Saida dos testes:
\`\`\`
$test_output
\`\`\`

Corrija o codigo para que todos os testes passem.
Rode os testes novamente apos cada correcao: ./vendor/bin/sail artisan test --compact
PROMPT

  echo "$prompt_file"
}

run_engine() {
  local prompt_file="$1"
  local log_file="$2"

  if [[ "$ENGINE" == "codex" ]]; then
    cat "$prompt_file" | codex exec --sandbox danger-full-access - 2>&1 | tee "$log_file"
  elif [[ "$ENGINE" == "claude" ]]; then
    env -u CLAUDECODE claude --dangerously-skip-permissions \
      -p "$(cat "$prompt_file")" \
      --output-format text \
      --verbose 2>&1 | tee "$log_file"
  fi
}

run_engine_with_token_wait() {
  local prompt_file="$1"
  local log_file="$2"
  local wait_attempt=1

  while true; do
    set +e
    run_engine "$prompt_file" "$log_file"
    local engine_status=$?
    set -e

    if has_token_exhaustion "$log_file"; then
      if ! wait_for_token_recovery "$wait_attempt"; then
        return 99
      fi

      wait_attempt=$((wait_attempt + 1))
      warn "Tentando novamente apos espera por limite/token..."
      continue
    fi

    return "$engine_status"
  done
}

run_phase() {
  local phase_file="$1"
  local phase_title="$2"
  local phase_num="$3"
  local total_phases="$4"
  local log_file="$LOG_DIR/${phase_file%.md}.log"
  local phase_start
  phase_start=$(date +%s)

  echo ""
  log "[$phase_num/$total_phases] $phase_title"

  local attempt=0
  local phase_success=false
  local prompt_file=""

  while [ "$attempt" -le "$MAX_RETRIES" ]; do
    attempt=$((attempt + 1))

    if [ "$attempt" -gt 1 ]; then
      warn "Tentativa $attempt/$((MAX_RETRIES + 1))..."
    fi

    if [ "$attempt" -eq 1 ]; then
      prompt_file=$(build_prompt_file "$phase_file")
    fi

    set +e
    run_engine_with_token_wait "$prompt_file" "$log_file"
    local engine_status=$?
    set -e

    if [ "$engine_status" -eq 0 ]; then
      phase_success=true
      break
    fi

    if [ "$engine_status" -eq 99 ]; then
      fail "$ENGINE nao retomou apos o limite configurado de esperas."
      break
    fi

    fail "$ENGINE retornou erro"

    if [ "$attempt" -le "$MAX_RETRIES" ]; then
      local test_output
      test_output=$(tail -50 "$log_file" 2>/dev/null || echo "Sem output disponivel")
      prompt_file=$(build_retry_prompt_file "$phase_file" "$test_output")
    fi
  done

  local phase_end
  phase_end=$(date +%s)
  local phase_duration=$((phase_end - phase_start))

  if $phase_success; then
    success "$phase_title — COMPLETA ($(format_duration "$phase_duration"))"

    if git rev-parse --is-inside-work-tree &> /dev/null 2>&1; then
      git add -A
      git commit -m "feat: implement $phase_title" --no-edit 2>/dev/null || true
      log "Commit criado no git"
    fi

    echo "$phase_file" >> "$PROGRESS_FILE"
    return 0
  fi

  fail "$phase_title — FALHOU apos $((MAX_RETRIES + 1)) tentativas ($(format_duration "$phase_duration"))"
  fail "Log disponivel em: $log_file"

  return 1
}

is_phase_done() {
  local phase_file="$1"
  [ -f "$PROGRESS_FILE" ] && grep -qF "$phase_file" "$PROGRESS_FILE"
}

main() {
  preflight_checks
  split_phases

  local total_phases
  total_phases=$(wc -l < "$MANIFEST")

  echo ""
  log "$total_phases fases encontradas"
  echo ""

  if [[ -n "$FROM_PHASE" ]]; then
    warn "Retomando a partir da fase $FROM_PHASE"
  fi

  if [[ -n "$ONLY_PHASE" ]]; then
    warn "Executando somente a fase $ONLY_PHASE"
  fi

  echo ""

  local num=0
  while IFS="|" read -r file title; do
    num=$((num + 1))

    if should_skip_phase_by_filter "$title"; then
      local reason
      reason=$(skip_reason_for_phase "$title")
      echo -e "  ${BLUE}[$num] $title (pulada por $reason)${NC}"
      continue
    fi

    if is_phase_done "$file"; then
      echo -e "  ${GREEN}[$num] $title (ja completada)${NC}"
    else
      echo -e "  ${YELLOW}[$num] $title${NC}"
    fi
  done < "$MANIFEST"

  echo ""
  read -p "Iniciar implementacao? (Y/n) " -n 1 -r
  echo
  [[ $REPLY =~ ^[Nn]$ ]] && exit 0

  local start_time
  start_time=$(date +%s)

  log "Inicio: $(format_timestamp "$start_time")"

  local current=0
  local failed_phases=()
  local skipped_phases=()
  local completed_phases=()

  while IFS="|" read -r file title; do
    current=$((current + 1))

    if should_skip_phase_by_filter "$title"; then
      local reason
      reason=$(skip_reason_for_phase "$title")
      log "Pulando $title ($reason)"
      skipped_phases+=("$title")
      continue
    fi

    if is_phase_done "$file"; then
      log "Pulando $title (ja completada)"
      skipped_phases+=("$title")
      continue
    fi

    if run_phase "$file" "$title" "$current" "$total_phases"; then
      completed_phases+=("$title")
    else
      failed_phases+=("$title")
      echo ""
      warn "Fase falhou: $title"
      read -p "Continuar para a proxima fase? (Y/n) " -n 1 -r
      echo
      [[ $REPLY =~ ^[Nn]$ ]] && break
    fi
  done < "$MANIFEST"

  local end_time
  end_time=$(date +%s)
  local total_duration=$((end_time - start_time))

  echo ""
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
  log "RELATORIO FINAL (engine: $ENGINE)"
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

  if [ "${#completed_phases[@]}" -gt 0 ]; then
    echo ""
    success "Completadas (${#completed_phases[@]}):"

    for phase in "${completed_phases[@]}"; do
      echo -e "    ${GREEN}$phase${NC}"
    done
  fi

  if [ "${#skipped_phases[@]}" -gt 0 ]; then
    echo ""
    log "Puladas (${#skipped_phases[@]}):"

    for phase in "${skipped_phases[@]}"; do
      echo -e "    $phase"
    done
  fi

  if [ "${#failed_phases[@]}" -gt 0 ]; then
    echo ""
    fail "Falharam (${#failed_phases[@]}):"

    for phase in "${failed_phases[@]}"; do
      echo -e "    ${RED}$phase${NC}"
    done

    echo ""
    fail "Verifique os logs em $LOG_DIR/"
  fi

  echo ""
  log "Inicio:        $(format_timestamp "$start_time")"
  log "Fim:           $(format_timestamp "$end_time")"
  log "Duracao total: $(format_duration "$total_duration")"
  echo ""
}

main