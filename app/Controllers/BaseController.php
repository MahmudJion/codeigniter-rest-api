<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use \Firebase\JWT\JWT;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Libraries\Uuid;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */

class BaseController extends ResourceController
{
	/**
	 * An array of helpers to be loaded automatically upon
	 * class instantiation. These helpers will be available
	 * to all other controllers that extend BaseController.
	 *
	 * @var array
	 */
	protected $helpers = [];

	/**
	 * Constructor.
	 *
	 * @param RequestInterface  $request
	 * @param ResponseInterface $response
	 * @param LoggerInterface   $logger
	 */
	public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
	{
		// Do Not Edit This Line
		parent::initController($request, $response, $logger);

		//--------------------------------------------------------------------
		// Preload any models, libraries, etc, here.
		//--------------------------------------------------------------------
		// E.g.: $this->session = \Config\Services::session();
	}

    protected static function buildResponse($type = 'success', $data = null, $message = null, $limit = null, $offset = null, $total = null)
    {
        $res = [
            'type' => $type
        ];

        if ($data != null) {
            $res['data'] = $data;
        }

        if ($message != null) {
            $res['message'] = $message;
        }

        if ($limit != null) {
            $res['limit'] = $limit;
        }

        if ($offset != null) {
            $res['offset'] = $offset;
        }

        if ($total != null) {
            $res['total'] = $total;
        }

        return $res;
    }

    public function getUniqueID($prefix = false)
    {
        $this->uuid = new Uuid();
        $prefixName = !empty($prefix) ? $prefix . '-' : '';
        return $prefixName . $this->uuid->v4();
    }

    protected function verifyToken($token = '')
    {
        if ($token) {
            $token = $token;
        } else {
            $token = $this->request->getHeaderLine('Token');
        }
        if ($token) {
            $key = $_ENV['JWT_SECRET'];
            try {
                $decoded = JWT::decode($token, $key, array('HS256'));
                return $decoded;
            } catch (\Exception $e) {
                // log the error echo $e->getmessage();
                $res = new \stdClass();
                $res->error = true;
                $res->message = $e->getmessage();
                return $res;
            }
        } else {
            $res = new \stdClass();
            $res->error = true;
            $res->message = 'Token not found';
            return $res;
        }
    }
}
