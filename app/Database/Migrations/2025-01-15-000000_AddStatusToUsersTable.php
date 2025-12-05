<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStatusToUsersTable extends Migration
{
    public function up()
    {
        $fields = [
            'status' => [
                'type'       => "ENUM('active','inactive')",
                'default'    => 'active',
                'after'      => 'role',
            ],
        ];

        $this->forge->addColumn('users', $fields);
        
        // Update all existing users to 'active' status
        $this->db->table('users')->update(['status' => 'active']);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'status');
    }
}

