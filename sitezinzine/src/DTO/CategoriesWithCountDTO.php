<?php 
namespace App\DTO;

class CategoriesWithCountDTO
{
 public function __construct(
    public readonly int $id,
    public readonly string $titre,
    public readonly string $descriptif,
    public readonly int $count
 )
 {
    
 }

}
