<?php

namespace App\Models\Owasp;

use CodeIgniter\Model;

class OwaspPropositionModel extends Model
{
    protected $DBGroup = 'owasp';
    protected $table = 'proposition';
    protected $primaryKey = 'id_prop';
}