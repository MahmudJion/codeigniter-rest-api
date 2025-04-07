<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\EmployeeModel;

class Employee extends ResourceController
{
    use ResponseTrait;

    /**
     * Get all employees.
     *
     * @return mixed
     */
    public function index()
    {
        $model = new EmployeeModel();
        $data['employees'] = $model->orderBy('id', 'DESC')->findAll();
        return $this->respond($data);
    }

    /**
     * Get a single employee by ID.
     *
     * @param int|null $id
     * @return mixed
     */
    public function show($id = null)
    {
        $model = new EmployeeModel();
        $data = $model->where('id', $id)->first();
        if ($data) {
            return $this->respond($data);
        } else {
            return $this->failNotFound('No employee found');
        }
    }

    /**
     * Create a new employee.
     *
     * @return mixed
     */
    public function create()
    {
        $model = new EmployeeModel();
        $validation = \Config\Services::validation();

        // Validation rules
        $validation->setRules([
            'name' => 'required|min_length[3]|max_length[100]',
            'email' => 'required|valid_email|is_unique[users.email]',
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->fail($validation->getErrors());
        }

        $data = [
            'name' => $this->request->getVar('name'),
            'email' => $this->request->getVar('email'),
        ];

        try {
            $model->insert($data);
            $response = [
                'status' => 201,
                'error' => null,
                'messages' => [
                    'success' => 'Employee created successfully',
                ],
            ];
            return $this->respondCreated($response);
        } catch (\Exception $e) {
            return $this->failServerError('Failed to create employee: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing employee.
     *
     * @param int|null $id
     * @return mixed
     */
    public function update($id = null)
    {
        $model = new EmployeeModel();
        $validation = \Config\Services::validation();

        // Validation rules
        $validation->setRules([
            'name' => 'required|min_length[3]|max_length[100]',
            'email' => 'required|valid_email',
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->fail($validation->getErrors());
        }

        $data = [
            'name' => $this->request->getVar('name'),
            'email' => $this->request->getVar('email'),
        ];

        if ($model->find($id)) {
            try {
                $model->update($id, $data);
                $response = [
                    'status' => 200,
                    'error' => null,
                    'messages' => [
                        'success' => 'Employee updated successfully',
                    ],
                ];
                return $this->respond($response);
            } catch (\Exception $e) {
                return $this->failServerError('Failed to update employee: ' . $e->getMessage());
            }
        } else {
            return $this->failNotFound('No employee found');
        }
    }

    /**
     * Delete an employee by ID.
     *
     * @param int|null $id
     * @return mixed
     */
    public function delete($id = null)
    {
        $model = new EmployeeModel();

        if ($model->find($id)) {
            try {
                $model->delete($id);
                $response = [
                    'status' => 200,
                    'error' => null,
                    'messages' => [
                        'success' => 'Employee successfully deleted',
                    ],
                ];
                return $this->respondDeleted($response);
            } catch (\Exception $e) {
                return $this->failServerError('Failed to delete employee: ' . $e->getMessage());
            }
        } else {
            return $this->failNotFound('No employee found');
        }
    }
}