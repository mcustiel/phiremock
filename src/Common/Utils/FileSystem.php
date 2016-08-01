<?php
namespace Mcustiel\Phiremock\Common\Utils;

class FileSystem
{
    public function getRealPath($path)
    {
        $path = $this->normalizePath($path);
        $tail = [];

        while (!file_exists($path)) {
            $path = explode('/', $path);
            array_unshift($tail, array_pop($path));
            $path = implode('/', $path);
        }

        return str_replace(
            DIRECTORY_SEPARATOR,
            '/',
            $path . '/' . implode(DIRECTORY_SEPARATOR, $tail)
        );
    }

    private function normalizePath($path)
    {
        $path = str_replace(DIRECTORY_SEPARATOR, '/', $path);
        if ($path[0] != '/') {
            $path = getcwd() . '/' . $path;
        }
        return $path;
    }
}
