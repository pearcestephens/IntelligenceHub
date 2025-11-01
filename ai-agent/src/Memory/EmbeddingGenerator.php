<?php

/**
 * Embedding Generator (Alias for Embeddings class)
 * Provides compatibility layer for tests expecting EmbeddingGenerator
 *
 * @package App\Memory
 * @author Ecigdis Limited (The Vape Shed)
 */

declare(strict_types=1);

namespace App\Memory;

/**
 * Alias class for backward compatibility with tests
 */
class EmbeddingGenerator extends Embeddings
{
    // Inherits all methods from Embeddings class
}
