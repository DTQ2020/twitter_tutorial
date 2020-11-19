<?php namespace App\Models;

use CodeIgniter\Model;

class Tweet_model extends Model
{

	public function add_auth($data) 
	{
		$this->db->table("twitter_auths")->insert($data);
	}

	public function get_auth($id) 
	{
		return $this->db->table("twitter_auths")->where("ID", $id)->get()->getRow();
	}


}

?>