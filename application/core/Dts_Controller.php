<?php

class Dts_Controller extends CI_Controller
{
    public function __construct(){
        parent::__construct();
    }

    public function to_api_message($error_code = 0, $error_message = "Success", $response = NULL){
        $this->output->set_content_type('application/json');
        $result = [
            'error_code' => $error_code,
            'error_message' => $error_message,
        ];
        if($response !== NULL && is_array($response)){
            $result['response'] = $response;
        }
        $this->output->set_output(json_encode($result, JSON_UNESCAPED_UNICODE));
    }

}