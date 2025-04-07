<?php

namespace App\Libraries;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\IncomingRequest;
use App\Models\AuthModel;
use App\Libraries\Imageprocessing;

class CustomerHandler
{
    private string $table;
    private object $config;
    private string $moduleFilePath;
    protected Imageprocessing $imageprocessing;

    public function __construct()
    {
        $this->config = new \Config\Tables();
        $this->table = $this->config->TABLE_CUSTOMER;
        $this->moduleFilePath = "assets/media/";
    }

    /**
     * Handles customer creation.
     *
     * @param IncomingRequest $request
     * @return object
     */
    public function create(IncomingRequest $request): object
    {
        $validation = \Config\Services::validation();
        $authModel = new AuthModel();
        $baseCon = new BaseController();
        $res = new \stdClass();

        // Validation rules
        $validation->setRules([
            'email_address' => [
                'label' => 'Email Address',
                'rules' => 'required|trim|valid_email',
                'errors' => ['required' => 'Please enter Email Address']
            ],
            'password' => [
                'label' => 'Password',
                'rules' => 'trim|required|min_length[8]|max_length[12]'
            ],
            'confirm_password' => [
                'label' => 'Confirm Password',
                'rules' => 'trim|required|matches[password]'
            ],
        ]);

        if ($validation->withRequest($request)->run()) {
            $users = $authModel->where(['email' => $request->getPost('email_address')])->findAll();

            if (empty($users)) {
                $customerData = [
                    'id' => $baseCon->getUniqueID($this->config->TABLE_CUSTOMER_PREFIX),
                    'status' => 'active',
                    'email' => $request->getPost('email_address'),
                    'password' => password_hash($request->getPost('password'), PASSWORD_DEFAULT),
                    'created_at' => date("Y-m-d H:i:s"),
                ];

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
            $res->errors = $validation->getErrors();
        }

        return $res;
    }

    /**
     * Handles customer survey data update.
     *
     * @param IncomingRequest $request
     * @return object
     */
    public function surveyData(IncomingRequest $request): object
    {
        $validation = \Config\Services::validation();
        $authModel = new AuthModel();
        $baseCon = new BaseController();
        $res = new \stdClass();

        // Validation rules
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
            $users = $authModel->where(['email' => $request->getPost('email_address')])->findAll();

            if (!empty($users)) {
                $user = $authModel->where(['email' => $request->getPost('email_address')])->first();
                $customer_id = $user['id'];

                $customerData = [
                    'id' => $customer_id,
                    'email' => $request->getPost('email_address'),
                    'first_name' => $request->getPost('first_name'),
                    'last_name' => $request->getPost('last_name'),
                    'gender' => $request->getPost('gender'),
                    'age' => $request->getPost('age'),
                    'height' => $request->getPost('height'),
                    'weight' => $request->getPost('weight'),
                    'diet_plan_list' => $request->getPost('diet_plan_list'),
                    'dietary_goals' => $request->getPost('dietary_goals'),
                    'ethical_rating' => $request->getPost('ethical_rating'),
                    'diet_restriction' => $request->getPost('diet_restriction'),
                ];

                // Handle profile picture upload
                if (!empty($_FILES) && isset($_FILES['profile_picture'])) {
                    $imageprocessing = new Imageprocessing();
                    $imageName = $imageprocessing->doUpload($this->moduleFilePath, 'profile_picture');
                    $photoPath = $imageprocessing->getImageCloudUrl($this->moduleFilePath, $imageName);
                    $customerData['profile_picture'] = $photoPath;
                }

                try {
                    $authModel->update($customer_id, $customerData);
                    $res->status = 'success';
                } catch (\Exception $e) {
                    $res->status = 'error';
                    $res->message = $e->getMessage();
                }
            } else {
                $res->status = 'error';
                $res->message = 'This email address is not registered in our system';
            }
        } else {
            $res->status = 'error';
            $res->message = 'Validation error';
            $res->errors = $validation->getErrors();
        }

        return $res;
    }
}
