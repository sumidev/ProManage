<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_name', 
        'file_path', 
        'mime_type', 
        'file_size', 
        'uploaded_by'
    ];

    // Polymorphic relation ka ulta hissa
    public function attachable()
    {
        return $this->morphTo();
    }
}
