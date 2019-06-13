<?php

class Crontab_model extends CI_Model
{
    protected $table_name = 't_crontab';

    protected $fileds = 'id,cron_name,cron_rule,cron_execution,cron_manager,cron_colony,status,createtime,updatetime,createuser,updateuser';

    public function __construct()
    {
        parent::__construct();
        $this->load->database(ENVIRONMENT);
    }

    public function get()
    {
        $this->db->select('id,cron_name,cron_rule,cron_execution,cron_manager,cron_colony,status,createtime,updatetime,createuser,updateuser');
//        $this->db->where('major',$major);
        $this->db->order_by('createtime', 'DESC');
        //$this->db->where('createtime >=',$time);
        $query = $this->db->get($this->table_name);
        return $query->result_array();
    }

    public function get_default_job()
    {
        $this->db->select($this->fileds);
        $this->db->where('status <', 3);
        $this->db->order_by('createtime', 'DESC');
        $query = $this->db->get($this->table_name);
        return $query->result_array();
    }


    public function get_by_colony($colony, $offset, $limit)
    {
        $this->db->select($this->fileds);
        $this->db->where('cron_colony', $colony);
        $this->db->where('status <', 3);
        $this->db->order_by('createtime', 'DESC');
        $query = $this->db->get($this->table_name, $limit, $offset);
        return $query->result_array();
    }

    public function add_job($colony, $rule, $manager, $execution)
    {
        $insert_value = [
            'cron_name' => $colony,
            'cron_rule' => $rule,
            'cron_execution' => $execution,
            'cron_manager' => $manager,
            'cron_colony' => $colony,
            'status' => 1,
            'createtime' => time()
        ];
        $this->db->insert($this->table_name, $insert_value);
        return $this->db->insert_id();
    }

    public function del_job($id)
    {
        $this->db->where('id', $id);
        $this->db->update($this->table_name, ['status' => 3]);
        return $this->db->affected_rows();
    }

    public function edit_job($id, $colony, $rule, $manager, $execution)
    {
        $edit_value = [
            'cron_name' => $colony,
            'cron_rule' => $rule,
            'cron_execution' => $execution,
            'cron_manager' => $manager,
            'cron_colony' => $colony,
            'status' => 1,
            'updatetime' => time()
        ];
        $this->db->where('id', $id);
        $this->db->update($this->table_name, $edit_value);
        return $this->db->affected_rows();
    }

    public function job_detail($id)
    {
        $this->db->select($this->fileds);
        $this->db->where('id', $id);
        $query = $this->db->get($this->table_name);
        return $query->result_array();
    }
}