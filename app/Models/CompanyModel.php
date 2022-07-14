<?php

namespace App\Models;

use CodeIgniter\Model;

class CompanyModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'companies';
    protected $primaryKey       = 'compay_id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['company_name','company_has_express','company_info'];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];
	
	/**
	 * @param string $name
	 * @return array
	 */
	public function getCompanyByName(string $name):array
	{
		$company = $this->where('company_name',[$name])->first();
		if(isset($company['company_info']))$company['company_info'] = json_decode($company['company_info'],true);
		return $company;
	}
	
	public function addCompany( string $name,bool $has_express,string $company_info):int
	{
		return $this->insert(['company_name'=>$name,'company_has_express'=>$has_express,'company_info'=>$company_info],true);
	}
	
}

