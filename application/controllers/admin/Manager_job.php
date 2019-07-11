<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Manager_job extends Dts_Controller
{
    const DEFAULT_LIMIT = 20;
    const DEFAULT_PAGE = 1;

    /**
     * 首页
     * @return bool
     */
    public function index()
    {
        $colony = $this->input->get('cron_colony');
        $page_num = !empty($this->input->get('page_num')) ? $this->input->get('page_num') : self::DEFAULT_PAGE;
        $page_size = $this->input->get('page_size');
        $limit = !empty($page_size) ? $page_size : self::DEFAULT_LIMIT;
        $offset = $this->_get_offset($page_num,$limit);

        $job_list = [];
        if (empty($colony)) {
            $job_list = $this->crontab_model->get_default_job();
            $resp = $this->_format_list($job_list);
            $this->to_api_message(0, 'Success', $resp);
            return true;
        }

        $job_list = $this->crontab_model->get_by_colony($colony, $limit, $offset);

        $resp = $this->_format_list($job_list);

        $this->to_api_message(0, 'Success', $resp);
        return true;
    }

    /**
     * 分页offset
     * @param $page_num
     * @param $limit
     * @return float|int
     */
    private function _get_offset($page_num,$limit)
    {
        return ($page_num - 1) * $limit;
    }

    /**
     * 状态变文字
     * @param $status
     * @return string
     */
    private function _get_status_name($status)
    {
        switch ($status){
            case 1 : return '无效'; break;
            case 2 : return '有效'; break;
            case 3 : return '删除'; break;
            default : return '无效';
        }
    }

    private function _format_time($timestamp)
    {
        return date("Y-m-d H:i",$timestamp);
    }


    /**
     * 格式化数据
     * @param $job_list
     * @return mixed
     */
    private function _format_list($job_list)
    {
        $resp = [];
        foreach ($job_list as $key=> $item) {
            $resp[$key]['id'] = $item['id'];
            $resp[$key]['status'] = $this->_get_status_name($item['status']);
            $resp[$key]['cron_name'] = $item['cron_name'];
            $resp[$key]['cron_rule'] = $item['cron_rule'];
            $resp[$key]['cron_execution'] = $item['cron_execution'];
            $resp[$key]['cron_manager'] = $item['cron_manager'];
            $resp[$key]['cron_colony'] = $item['cron_colony'];
            $resp[$key]['createtime'] = $this->_format_time($item['createtime']);
        }
        return $resp;
    }

    /**
     * 新加任务
     * @return bool
     */
    public function add()
    {
        $post_data = [
            [
                'field' => 'cron_colony',//集群名
                'label' => 'Colony',
                'rules' => 'required',
                'errors' => [
                    'required' => 'You must provide a %s.',
                ]
            ],
            [
                'field' => 'cron_rule',
                'label' => 'Rule',
                'rules' => 'required',
            ],
            [
                'field' => 'cron_name',
                'label' => 'Rule',
                'rules' => 'required',
            ],
            [
                'field' => 'cron_manager',
                'label' => 'Manager',
                'rules' => 'required',
            ],
            [
                'field' => 'cron_execution',
                'label' => 'Execution',
                'rules' => 'required',
            ],
        ];

        $this->form_validation->set_rules($post_data);

        if ($this->form_validation->run() == FALSE) {
            $this->to_api_message(-1, 'Error', ['data' => validation_errors()]);
            return false;
        }
        $colony = $this->input->post('cron_colony');
        $name = $this->input->post('cron_name');
        $rule = trim($this->input->post('cron_rule'));
        $manager = $this->input->post('cron_manager');
        $execution = $this->input->post('cron_execution');
        $add_resp = $this->crontab_model->add_job($colony, $rule, $manager, $execution,$name);
        if ($add_resp > 0) {
            $this->to_api_message(0, 'Success', ['data' => ['job_id' => $add_resp]]);
            return true;
        }
        $this->to_api_message(-1, 'Error', ['data' => ['job_id' => '插入数据失败']]);
        return false;
    }

    /**
     * 删除任务
     */
    public function delete()
    {
        $post_data = [
            [
                'field' => 'id',
                'label' => 'Id',
                'rules' => 'required',
                'errors' => [
                    'required' => 'You must provide a %s.',
                ]
            ]
        ];

        $this->form_validation->set_rules($post_data);

        if ($this->form_validation->run() == FALSE) {
            $this->to_api_message(-1, 'Error', ['data' => validation_errors()]);
            return false;
        }
        $id = $this->input->post('id');

        $del_resp = $this->crontab_model->del_job($id);
        if ($del_resp > 0) {
            $this->to_api_message(0, 'Success', ['data' => 'del success']);
            return true;
        }
        $this->to_api_message(-1, 'Error', ['data' => 'del error']);
        return false;
    }

    /**
     * 编辑任务
     * @return bool
     */
    public function edit()
    {
        $post_data = [
            [
                'field' => 'id',
                'label' => 'Id',
                'rules' => 'required',
            ],
            [
                'field' => 'cron_colony',
                'label' => 'Colony',
                'rules' => 'required',
            ],
            [
                'field' => 'cron_rule',
                'label' => 'Rule',
                'rules' => 'required',
            ],
            [
                'field' => 'cron_manager',
                'label' => 'Manager',
                'rules' => 'required',
            ],
            [
                'field' => 'cron_execution',
                'label' => 'Execution',
                'rules' => 'required',
            ],
            [
                'field' => 'status',
                'label' => 'Status',
                'rules' => 'required',
            ],
            [
                'field' => 'cron_name',
                'label' => 'Cronname',
                'rules' => 'required',
            ],
        ];

        $this->form_validation->set_rules($post_data);

        if ($this->form_validation->run() == FALSE) {
            $this->to_api_message(-1, 'Error', ['data' => validation_errors()]);
            return false;
        }
        $id = $this->input->post('id');
        $colony = $this->input->post('cron_colony');
        $rule = trim($this->input->post('cron_rule'));
        $manager = $this->input->post('cron_manager');
        $execution = $this->input->post('cron_execution');
        $name = $this->input->post('name');
        $status = $this->input->post('status');
        $edit_resp = $this->crontab_model->edit_job($id, $colony, $rule, $manager, $execution,$status,$name);
        if ($edit_resp > 0) {
            $this->to_api_message(0, 'Success', ['data' => ['job_id' => $edit_resp]]);
            return true;
        }
        $this->to_api_message(-1, 'Error', ['data' => ['job_id' => '修改数据失败']]);
        return false;
    }

    /**
     * 任务详情
     */
    public function detail()
    {
        $post_data = [
            [
                'field' => 'id',
                'label' => 'Id',
                'rules' => 'required',
                'errors' => [
                    'required' => 'You must provide a %s.',
                ]
            ]
        ];

        $this->form_validation->set_rules($post_data);

        if ($this->form_validation->run() == FALSE) {
            $this->to_api_message(-1, 'Error', ['data' => validation_errors()]);
            return false;
        }
        $id = $this->input->post('id');

        $detail_resp = $this->crontab_model->job_detail($id);

        $this->to_api_message(-1, 'Error', ['data' => $detail_resp]);
        return true;
    }
}