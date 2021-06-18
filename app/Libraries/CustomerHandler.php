<?php

namespace App\Libraries;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\IncomingRequest;
use App\Models\AuthModel;
use App\Libraries\Imageprocessing;

class CustomerHandler
{

    private $table;
    private $config;
    private $moduleFilePath;
    protected $imageprocessing;

    public function __construct()
    {
        $this->config = new \Config\Tables();
        $this->table = $this->config->TABLE_CUSTOMER;
        $this->moduleFilePath = "assets/media/";
    }

    public function create($request)
    {
        $validation =  \Config\Services::validation();
        $authModel = new AuthModel();
        $baseCon = new BaseController();
        $res = new \stdClass();

        $validation->setRules([
            'email_address' => ['label' => 'Email Address', 'rules' => 'required|trim|valid_email', 'errors' => ['required' => 'Please enter Email Address']],
            'password' => ['label' => 'Password', 'rules' => 'trim|required|min_length[8]|max_length[12]'],
            'confirm_password' => ['label' => 'Confirm Password', 'rules' => 'trim|required|matches[password]'],
        ]);

        if ($validation->withRequest($request)->run()) {

            $users = $authModel->where(array('email' => $request->getPost('email_address')))->findAll();
            if (count($users) == 0) {
                $customerData = array();
                $customerData['id'] = $baseCon->getUniqueID($this->config->TABLE_CUSTOMER_PREFIX);
                $customerData['status'] = 'active';
                $customerData['email'] = $request->getPost('email_address');
                $customerData['password'] = password_hash($request->getPost('password'), PASSWORD_DEFAULT);
                $customerData['created_at'] = date("Y-m-d H:i:s");

                try {
                    $authModel->insert($customerData);
                    $res->status = 'success';
                } catch (\Exception $e) {
                    $res->status = 'error';
                    $res->message = $e->getMessage();
                }
            } else {
                $res->status = 'error';
                $res->message = 'This email address is already registered in our system';
            }
        } else {
            $res->status = 'error';
            $res->message = 'Validation error';
            $res->errors = join(',', $validation->getErrors());
        }

        return $res;

    }

    public function surveyData($request)
    {
        $validation =  \Config\Services::validation();
        $authModel = new AuthModel();
        $baseCon = new BaseController();
        $res = new \stdClass();

        $validation->setRules([
            'first_name' => [
                'label' => 'First Name',
                'rules' => 'required|trim',
                'errors' => ['required' => 'Please enter First Name']
            ],
            'last_name' => [
                'label' => 'Last Name',
                'rules' => 'required|trim',
                'errors' => ['required' => 'Please enter Last Name']
            ]
        ]);

        if ($validation->withRequest($request)->run()) {
            $users = $authModel->where(array('email' => $request->getPost('email_address')))->findAll();

            if (count($users) != 0) {
                $users = $authModel->where(array('email' => $request->getPost('email_address')))->first();
                $customer_id =  $users['id'];

                $customerData = array();
                $customerData['id'] = $customer_id;
                $customerData['email'] = $request->getPost('email_address');
                $customerData['first_name'] = $request->getPost('first_name');
                $customerData['last_name'] = $request->getPost('last_name');

                if (!empty($_FILES) && $_FILES['profile_picture']) {
                    $imageprocessing = new Imageprocessing();
                    $imageName = $imageprocessing->doUpload($this->moduleFilePath,'profile_picture');
                    $photoPath = $imageprocessing->getImageCloudUrl($this->moduleFilePath, $imageName);
                    $customerData['profile_picture'] = $photoPath;
                }

                $customerData['gender'] = $request->getPost('gender');
                $customerData['age'] = $request->getPost('age');
                $customerData['height'] = $request->getPost('height');
                $customerData['weight'] = $request->getPost('weight');
                $customerData['diet_plan_list'] = $request->getPost('diet_plan_list');
                $customerData['dietary_goals'] = $request->getPost('dietary_goals');
                $customerData['ethical_rating'] = $request->getPost('ethical_rating');
                $customerData['diet_restriction'] = $request->getPost('diet_restriction');

                try {
                    $authModel->update($customer_id, $customerData);
                    $res->status = 'success';
                } catch (\Exception $e) {
                    $res->status = 'error';
                    $res->message = $e->getMessage();
                }
            } else if(count($users) == 0) {
                $res->status = 'error';
                $res->message = 'This email address is not registered in our system';
            }
        } else {
            $res->status = 'error';
            $res->message = 'Validation error';
            $res->errors = join(',', $validation->getErrors());
        }

        return $res;

    }
}
