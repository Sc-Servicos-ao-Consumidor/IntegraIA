<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'marca',
        'escolha_embalagem',
        'prompt_especificacao_embalagens',
        'prompt_uso_informacoes_produto',
        'especificacao_produto',
        'perfil_sabor',
        'descricao_tabela_nutricional',
        'descricao_lista_ingredientes',
        'descricao_modos_preparo',
        'descricao_rendimentos',
        'embalagem_tipo',
        'embalagem_descricao',
        'quantidade_caixa',
        'peso_liquido',
        'peso_bruto',
        'validade',
        'valor',
        'desconto',
        'informacao_adicional',
        'ean',
        'qr_code',
        'url_rede_social',
        'catalogo',
        'lancamento',
        'status',
    ];

    protected $casts = [
        'peso_liquido' => 'decimal:2',
        'peso_bruto' => 'decimal:2',
        'valor' => 'decimal:2',
        'desconto' => 'decimal:2',
        'catalogo' => 'boolean',
        'lancamento' => 'boolean',
        'status' => 'boolean',
    ];

    /**
     * Get the product that owns this detail record.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get available packaging types.
     */
    public static function getPackagingTypes(): array
    {
        return [
            'kg' => 'Quilograma',
            'g' => 'Grama',
            'l' => 'Litro',
            'ml' => 'Mililitro',
            'c' => 'Caixa',
            'cx' => 'Caixa',
            'un' => 'Unidade',
            'pct' => 'Pacote',
            'sache' => 'Sachê',
            'lata' => 'Lata',
            'frasco' => 'Frasco',
        ];
    }

    /**
     * Get formatted price with discount if applicable.
     */
    public function getFormattedPriceAttribute(): string
    {
        if ($this->desconto && $this->desconto > 0) {
            $discountedPrice = $this->valor - ($this->valor * $this->desconto / 100);
            return 'R$ ' . number_format($discountedPrice, 2, ',', '.');
        }
        
        return 'R$ ' . number_format($this->valor, 2, ',', '.');
    }

    /**
     * Get original price formatted.
     */
    public function getOriginalPriceAttribute(): string
    {
        return 'R$ ' . number_format($this->valor, 2, ',', '.');
    }

    /**
     * Check if product has discount.
     */
    public function hasDiscount(): bool
    {
        return $this->desconto && $this->desconto > 0;
    }

    /**
     * Get weight information formatted.
     */
    public function getFormattedWeightAttribute(): ?string
    {
        if ($this->peso_liquido) {
            return number_format($this->peso_liquido, 2, ',', '.') . ' ' . ($this->embalagem_tipo ?? 'kg');
        }
        
        return null;
    }
}