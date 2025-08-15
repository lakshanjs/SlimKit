<?php
declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use App\Services\Upload;
use App\Support\Validation;
use Respect\Validation\Validator as v;

class FileController
{
    public function __construct(private Twig $view, private Upload $upload)
    {
    }

    public function showForm(Request $request, Response $response): Response
    {
        $nonce = $request->getAttribute('cspNonce');
        return $this->view->render($response, 'files/upload.twig', ['csp_nonce' => $nonce]);
    }

    public function upload(Request $request, Response $response): Response
    {
        $files = $request->getUploadedFiles();
        $errors = Validation::validate($files, [
            'file' => v::uploaded()->size('1MB', null)
        ]);

        $nonce = $request->getAttribute('cspNonce');

        if ($errors) {
            return $this->view->render($response->withStatus(422), 'files/upload.twig', [
                'errors' => $errors,
                'csp_nonce' => $nonce,
            ]);
        }

        $directory = __DIR__ . '/../../storage/uploads';
        $this->upload->upload($files['file'], $directory);
        return $response->withHeader('Location', '/files/upload')->withStatus(302);
    }

    public function apiUpload(Request $request, Response $response): Response
    {
        $files = $request->getUploadedFiles();
        if (!isset($files['file'])) {
            $response->getBody()->write(json_encode(['error' => 'No file provided']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        $directory = __DIR__ . '/../../storage/uploads';
        $filename = $this->upload->upload($files['file'], $directory);
        $response->getBody()->write(json_encode(['filename' => $filename]));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
