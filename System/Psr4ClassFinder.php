<?php
namespace ComposerPack\System;

// https://stackoverflow.com/questions/22761554/php-get-all-class-names-inside-a-particular-namespace
class Psr4ClassFinder
{

    public static function getClassesInNamespace($namespace)
    {
        $ns = self::getNamespaceDirectory($namespace);
        if(!empty($ns))
        {
            $files = scandir(self::getNamespaceDirectory($namespace));
        }
        else if(file_exists(get("base_dir") . '/' . str_replace('\\', '/', $namespace)))
        {
            $files = scandir(get("base_dir") . '/' . str_replace('\\', '/', $namespace));
        }
        if(empty($files))
            $files = [];

        $classes = array_map(function($file) use ($namespace){
            return $namespace . '\\' . str_replace('.php', '', $file);
        }, $files);

        return array_filter($classes, function($possibleClass){
            return class_exists($possibleClass);
        });
    }

    private static function getDefinedNamespaces()
    {
        $composerJsonPath = get("base_dir") . '/composer.json';
        $composerConfig = json_decode(file_get_contents($composerJsonPath));

        //Apparently PHP doesn't like hyphens, so we use variable variables instead.
        $psr4 = "psr-4";
        return (array) $composerConfig->autoload->$psr4;
    }

    private static function getNamespaceDirectory($namespace)
    {
        $composerNamespaces = self::getDefinedNamespaces();

        if(!is_array($composerNamespaces))
            $composerNamespaces = [$composerNamespaces];
        foreach($composerNamespaces as $underNamespace => $composerNamespace) {

            $namespaceFragments = explode('\\', $namespace);
            $undefinedNamespaceFragments = [];

            while ($namespaceFragments) {
                $possibleNamespace = implode('\\', $namespaceFragments) . '\\';
                if(startsWith($possibleNamespace, $underNamespace)) {
                    $possibleNamespace = substr($possibleNamespace, strlen($underNamespace));
                    if(empty($possibleNamespace))
                        $possibleNamespace = "";
                }

                if (array_key_exists($possibleNamespace, $composerNamespace)) {
                    return realpath(get("base_dir") . '/' . $composerNamespace[$possibleNamespace] . implode('/', $undefinedNamespaceFragments));
                }
                else if(in_array($possibleNamespace, $composerNamespace)) {
                    return realpath(get("base_dir") . '/' . $possibleNamespace . implode('/', $undefinedNamespaceFragments));
                }

                $undefinedNamespaceFragments[] = array_pop($namespaceFragments);
            }
        }

        return false;
    }

    public static function removeNamespace($class)
    {
        $namespaces = self::getDefinedNamespaces();
        $namespaces = array_keys($namespaces);
        usort($namespaces, function($a, $b) {
            return strlen($b) - strlen($a);
        });
        foreach ($namespaces as $namespace)
        {
            if(strpos($class, $namespace) === 0)
            {
                return substr($class, strlen($namespace));
            }
        }
    }
}