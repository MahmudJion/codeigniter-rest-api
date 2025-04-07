<?php

namespace App\Controllers;

use App\Libraries\CustomerHandler;
use CodeIgniter\Controller;
use Firebase\JWT\JWT;
use App\Models\AuthModel;

class Auth extends BaseController
{
    protected $validation;

    public function __construct()
    {
        $this->modelName = new AuthModel();
        $this->format = 'json';
        $this->validation = \Config\Services::validation();
    }

    /**
     * Get all customers.
     *
     * @return mixed
     */
    public function index()
    {
        $record = $this->modelName->findAll();
        $response = parent::buildResponse('success', $record, 'All Customers', 20, 1, 100);
        return $this->respond($response, 200);
    }

    /**
     * Handle user login.
     *
     * @return mixed
     */
    public function create()
    {
        if ($this->validatedRegularLogin()) {
            $users = $this->modelName->where([
                'status' => 'active',
                'email' => $this->request->getPost('email_address'),
            ])->findAll();

            if (!empty($users)) {
                $userArray = $users[0];
                return $this->loginResponse($userArray);
            } else {
                return $this->failForbidden("No user found with this email address", 403);
            }
        } else {
            return $this->failValidationError($this->validation->getErrors());
        }
    }

    /**
     * Validate login input.
     *
     * @return bool
     */
    private function validatedRegularLogin(): bool
    {
        $this->validation->setRules([
            'email_address' => ['label' => 'Email Address', 'rules' => 'trim|required|valid_email'],
            'password' => ['label' => 'Password', 'rules' => 'required|min_length[8]|max_length[12]'],
        ]);

        return $this->validation->withRequest($this->request)->run();
    }

    /**
     * Handle user sign-out.
     *
     * @return mixed
     */
    public function signOut()
    {
        $customerData = $this->verifyToken();
        if (isset($customerData->error)) {
            return $this->failForbidden($customerData->message, 403);
        } else {
            $response = parent::buildResponse('success', false, 'Customer signed out successfully');
            return $this->respond($response, 200);
        }
    }

    /**
     * Handle user sign-up.
     *
     * @return mixed
     */
    public function signUp()
    {
        $customerHandler = new CustomerHandler();
        $res = $customerHandler->create($this->request);

        if ($res->status === 'success') {
            $response = parent::buildResponse('success', false, 'Successfully Registered.');
            return $this->respond($response, 200);
        } else {
            if (isset($res->errors)) {
                return $this->failValidationError($res->errors);
            } else {
                return $this->failForbidden($res->message, 403);
            }
        }
    }

    /**
     * Handle password reset.
     *
     * @return mixed
     */
    public function resetPassword()
    {
        $this->validation->setRules([
            'email_address' => ['label' => 'Email Address', 'rules' => 'required|trim|valid_email'],
            'new_password' => ['label' => 'New Password', 'rules' => 'trim|required|min_length[8]|max_length[12]'],
            'confirm_new_password' => ['label' => 'Confirm New Password', 'rules' => 'trim|required|matches[new_password]'],
        ]);

        if ($this->validation->withRequest($this->request)->run()) {
            $users = $this->modelName->where(['email' => $this->request->getPost('email_address')])->findAll();

            if (!empty($users)) {
                $userInfoArray = $users[0];
                $this->modelName->update($userInfoArray['id'], [
                    'password' => password_hash($this->request->getPost('new_password'), PASSWORD_DEFAULT),
                ]);
                $response = parent::buildResponse('success', false, 'Password Changed Successfully');
                return $this->respond($response, 200);
            } else {
                return $this->failForbidden("This email address is not registered in our system", 403);
            }
        } else {
            return $this->failValidationError($this->validation->getErrors());
        }
    }

    /**
     * Handle forgot password.
     *
     * @return mixed
     */
    public function forgotPassword()
    {
        $this->validation->setRules([
            'email_address' => ['label' => 'Email Address', 'rules' => 'required|trim|valid_email'],
            'verify_url' => ['label' => 'Verify URL', 'rules' => 'required|trim'],
        ]);

        if ($this->validation->withRequest($this->request)->run()) {
            $verifyUrl = $this->request->getPost('verify_url');
            $users = $this->modelName->where(['email' => $this->request->getPost('email_address')])->findAll();

            if (!empty($users)) {
                $user_info = $users[0];
                $customer = (object) [
                    'id' => $user_info['id'],
                    'email' => $user_info['email'],
                ];
                $payload = $this->buildJWTPayload($customer);
                $key = $_ENV['JWT_SECRET'];
                $jwtToken = JWT::encode($payload, $key);
                $url = $verifyUrl . $jwtToken;

                $data = ['url' => $url];
                $response = parent::buildResponse('success', $data);
                return $this->respond($response, 200);
            } else {
                return $this->failForbidden("This email address is not registered in our system", 403);
            }
        } else {
            return $this->failValidationError($this->validation->getErrors());
        }
    }

    /**
     * Build JWT payload.
     *
     * @param object $customer
     * @return object
     */
    private function buildJWTPayload(object $customer): object
    {
        return (object) [
            'iss' => $_ENV['SITE_TITLE'],
            'iat' => time(),
            'exp' => strtotime("+24 hours"),
            'aud' => $_ENV['SITE_DOMAIN'],
            'sub' => $customer->email,
            'customer' => $customer,
        ];
    }

    /**
     * Handle login response.
     *
     * @param array $userArray
     * @return mixed
     */
    private function loginResponse(array $userArray)
    {
        if (password_verify($this->request->getPost('password'), $userArray['password'])) {
            $data = $this->verifyAuth($userArray);
            $response = parent::buildResponse('success', $data);
            return $this->respond($response, 200);
        } else {
            $response = parent::buildResponse('error', null, 'Invalid Password');
            return $this->respond($response, 401);
        }
    }

    /**
     * Verify user authentication.
     *
     * @param array $data
     * @return object
     */
    private function verifyAuth(array $data): object
    {
        $customer = (object) [
            'id' => $data['id'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'profile_picture' => $data['profile_picture'],
            'gender' => $data['gender'],
            'age' => $data['age'],
            'height' => $data['height'],
            'weight' => $data['weight'],
            'life_style_info' => $data['diet_plan_list'],
            'dietary_goals' => $data['dietary_goals'],
            'ethical_rating' => $data['ethical_rating'],
            'diet_restriction' => $data['diet_restriction'],
        ];

        $payload = $this->buildJWTPayload($customer);
        $key = $_ENV['JWT_SECRET'];
        $jwtToken = JWT::encode($payload, $key);

        return (object) [
            'customer' => $customer,
            'token' => $jwtToken,
        ];
    }
}