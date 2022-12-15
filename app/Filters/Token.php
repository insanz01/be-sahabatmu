<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class Token implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $request = \Config\Services::request();
        $db = \Config\Database::connect();

        $header = $request->headers();
        try {
            $token = $header["X-Api-Key"]->getValue();
        } catch (Exception $e) {
            $token = NULL;
            return Services::response()
                ->setJSON(["message" => "Token Required"])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }

        if (!$token) {
            return Services::response()
                ->setJSON(["message" => "Token Required"])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        } else {
            $query = $db->query("SELECT user_id FROM tokens WHERE token = '$token'");

            $result = $query->getRow();

            $db->close();

            if (!$result) {
                return Services::response()
                    ->setJSON(["message" => "Invalid Token"])
                    ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
            }
        }
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}
