<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterMaterialsTableFilePath extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('materials', [
            'file_path' => [
                'type' => 'TEXT',
            ],
        ]);
    }

    public function down()
    {
        //
    }
}
