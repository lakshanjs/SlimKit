<?php
declare(strict_types=1);

namespace App\View;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use App\Middleware\CsrfGuardMiddleware;

class TwigExtensions extends AbstractExtension
{
    public function __construct(private CsrfGuardMiddleware $csrf)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('csrf_field', [$this, 'csrfField'], ['is_safe' => ['html']]),
        ];
    }

    public function csrfField(): string
    {
        $token = $this->csrf->getToken();
        return sprintf(
            '<input type="hidden" name="%s" value="%s"><input type="hidden" name="%s" value="%s">',
            $token['nameKey'],
            $token['name'],
            $token['valueKey'],
            $token['value']
        );
    }
}
