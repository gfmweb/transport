<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCompanyTable extends Migration
{
    public function up()
    {
       $this->forge->addField([
		  'company_id'=>
			  [
			  'type'           =>'INT',
			  'constraint'     => 5,
			  'unsigned'       => true,
			  'auto_increment' => true,
			  'comment'        => 'Простой ID компании',
			  ],
	       'company_name'=>
		       [
			       'type'           =>'VARCHAR',
			       'constraint'     => 32,
			       'null'           => false,
			       'comment'        => 'Имя компании',
		       ],
	       'company_has_express'=>
		       [
			       'type'           =>'BOOLEAN',
			       'default'        => false,
			       'comment'        => 'Признак наличия быстрой доставки',
		       ],
	       'company_info'=>
		       [
			       'type'           =>'JSON',
			       'null'           => false,
			       'comment'        => 'Прайсы инфо дополнительные поля для расчета',
		       ],
	       'created_at datetime default current_timestamp',
	       'updated_at datetime default current_timestamp on update current_timestamp',
       ]);
	    $this->forge->addPrimaryKey('company_id');
	    $this->forge->createTable('companies');
    }

    public function down()
    {
        $this->forge->dropTable('companies');
    }
}
