<?php

namespace App\Controllers;

use App\Libraries\CustomerHandler;
use CodeIgniter\Controller;
use \Firebase\JWT\JWT;
use App\Models\AuthModel;

class Auth extends BaseController
{
    protected $validation = null;
    public function __construct()
    {
        $this->modelName = new AuthModel();
        $this->format = 'json';
        $this->validation = \Config\Services::validation();
    }

    public function index()
    { // GET
        $this->modelName = new AuthModel();
        $record = $this->modelName->findAll();
        $response = parent::buildResponse('success', $record, 'All Customer', 20, 1, 100);
        return $this->respond($response, 200);
    }

    public function create()
    { // POST
        if($this->validatedRegularLogin()) {
            $this->modelName = new AuthModel();
            $users = $this->modelName->where(array('status' => 'active', 'email' => $this->request->getPost('email_address')))->findAll();
            if ($users) {
                $userArray = $users[0];
                return $this->loginResponse($userArray);
            } else {
                return $this->failForbidden("No user found with this email address", 500);
            }
        }
        else {
            $errors = join(',', $this->validation->getErrors());
            return $this->failValidationError($errors);
        }
    }

    private function validatedRegularLogin() {
        $this->validation->setRules([
            'email_address' => ['label' => 'Email Address', 'rules' => 'trim|required'],
            'password' => ['label' => 'Password', 'rules' => 'required|min_length[8]|max_length[12]']
        ]);
        if ($this->validation->withRequest($this->request)->run()) {
            return true;
        } else {
            return false;
        }
    }

    public function signOut()
    {
        $customerData = $this->verifyToken();
        if (isset($customerData->error)) {
            return $this->failForbidden($customerData->message, 500);
        } else {
            $response = parent::buildResponse('success', false, 'Customer sign out successfully');
            return $this->respond($response, 200);
        }
    }

    public function signUp()
    {
        $customerHandler = new CustomerHandler();
        $res = $customerHandler->create($this->request);

        if ($res->status == 'success') {
            $response = parent::buildResponse('success', false, 'Successfully Registered.');
            return $this->respond($response, 200);
        } else {
            if(isset($res->errors)) return $this->failValidationError($res->errors);
            else return $this->failForbidden($res->message, 500);
        }
    }

    public function userData()
    {
        $customerHandler = new CustomerHandler();
        $res = $customerHandler->surveyData($this->request);

        if ($res->status == 'success') {
            $response = parent::buildResponse('success', false, 'Successfully Registered.');
            return $this->respond($response, 200);
        } else {
            if(isset($res->errors)) return $this->failValidationError($res->errors);
            else return $this->failForbidden($res->message, 500);
        }
    }

    public function resetPassword()
    {
        $this->validation->setRules([
            'email_address' => ['label' => 'Email Address', 'rules' => 'required|trim|valid_email', 'errors' => ['required' => 'Please enter Email Address']],
            'new_password' => ['label' => 'New Password', 'rules' => 'trim|required|min_length[8]|max_length[12]'],
            'confirm_new_password' => ['label' => 'Confirm New Password', 'rules' => 'trim|required|matches[new_password]'],
        ]);
        if ($this->validation->withRequest($this->request)->run()) {
            $this->modelName = new AuthModel();
            $users = $this->modelName->where(array('email' => $this->request->getPost('email_address')))->findAll();
            if (count($users) > 0) {
                $userInfoArray = $users[0];
                $this->modelName->update($userInfoArray['id'], array('password' => password_hash($this->request->getPost('new_password'), PASSWORD_DEFAULT)));
                $response = parent::buildResponse('success', false, 'Password Changed Successfully');
                return $this->respond($response, 200);
            } else {
                return $this->failForbidden("This email address is not registered in our system", 500);
            }
        } else {
            $errors = join(',', $this->validation->getErrors());
            return $this->failValidationError($errors);
        }
    }

    public function forgotPassword()
    {
        $rules = [
            'email_address' => ['label' => 'Email Address', 'rules' => 'required|trim|valid_email', 'errors' => ['required' => 'Please enter Email Address']],
            'verify_url' => ['label' => 'Verify URL', 'rules' => 'required|trim', 'errors' => ['required' => 'Please enter Verify URL']],
        ];
        $this->validation->setRules($rules);

        if ($this->validation->withRequest($this->request)->run()) {
            $verifyUrl = $this->request->getPost('verify_url');

            $this->modelName = new AuthModel();
            $users = $this->modelName->where(array('email' => $this->request->getPost('email_address')))->findAll();
            if (count($users) > 0) {
                $user_info = $users[0];
                $customer = new \stdClass();
                $customer->id = $user_info['id'];
                $customer->email = $user_info['email'];
                $payload = $this->buildJWTPayload($customer);
                $key = $_ENV['JWT_SECRET'];
                $jwtToken = JWT::encode($payload, $key);
                $url = $verifyUrl . $jwtToken;

                $data = ['url' => $url];
                $response = parent::buildResponse('success', $data);
                return $this->respond($response, 200);
            } else {
                return $this->failForbidden("This email address is not registered in our system", 500);
            }
        } else {
            $errors = join(',', $this->validation->getErrors());
            return $this->failValidationError($errors);
        }
    }

    private function loginResponse($userArray)
    {
        if (password_verify($this->request->getPost('password'), $userArray['password'])) {
            $data = $this->verifyAuth($userArray);
            $response = parent::buildResponse('success', $data);
            return $this->respond($response, 200);
        } else {
            $response = parent::buildResponse('error', null, 'Invalid Password');
            return $this->respond($response, 200);
        }
    }

    private function verifyAuth($data)
    {
        // check database
        $customer = new \stdClass();
        $customer->id = $data['id'];
        $customer->first_name = $data['first_name'];
        $customer->last_name = $data['last_name'];
        $customer->email = $data['email'];
        $customer->profile_picture = $data['profile_picture'];
        $customer->gender = $data['gender'];
        $customer->age = $data['age'];
        $customer->height = $data['height'];
        $customer->weight = $data['weight'];
        $customer->life_style_info = $data['diet_plan_list'];
        $customer->dietary_goals = $data['dietary_goals'];
        $customer->ethical_rating = $data['ethical_rating'];
        $customer->diet_restriction = $data['diet_restriction'];
        $payload = $this->buildJWTPayload($customer);
        $key = $_ENV['JWT_SECRET'];
        $jwtToken = JWT::encode($payload, $key);
        $obj = new \stdClass();
        $obj->customer = $customer;
        $obj->token = $jwtToken;
        return $obj;
    }

    private function buildJWTPayload($customer)
    {
        $obj = new \stdClass();
        $obj->iss = $_ENV['SITE_TITLE'];
        $obj->iat = time();
        $obj->exp = strtotime("+ 24 hour");
        $obj->aud = $_ENV['SITE_DOMAIN'];
        $obj->sub = $customer->email;
        $obj->customer = $customer;
        return $obj;
    }
}