<?php

namespace ComposerPack\Controller;

use ComposerPack\Module\Package\Branch;
use ComposerPack\Module\Package\Repository;
use ComposerPack\System\Settings;

class PackagesController extends DefaultController
{

    public function actionIndex()
    {
        $repos = [];
        $repos["packages"] = [];
        $repos["notify"] = Settings::get("base_link")."downloads/%package%";
        $repos["notify-batch"] = Settings::get("base_link")."downloads/";
        $repos["providers-url"] = Settings::get("base_link")."p/%package%-%hash%.json";
        $repos["search"] = Settings::get("base_link")."search.json?q=%query%&type=%type%";
        $repos["provider-includes"] = [];
        $repos["provider-includes"]["p/provider-%hash%.json"] = [];
        $repos["provider-includes"]["p/provider-%hash%.json"]["sha256"] = "";
        $repositories = new Repository();
        $repositories = $repositories->result();
        $providers = $this->repository($repositories);
        $repos["provider-includes"]["p/provider-%hash%.json"]["sha256"] = hash("sha256", json_encode($providers));
        echo json_encode($repos);
        die();
    }

    public function actionProvider($hash)
    {
        $repositories = new Repository();
        $repositories = $repositories->result();
        $providers = $this->repository($repositories);
        echo json_encode($providers);
        die();
    }

    public function actionRepository($author, $repository, $hash)
    {
        $repo = new Repository($author, $repository);
        $branches = new Branch();
        $branches = $branches->where("author", $repo["author"])->where("repo", $repo["repo"])->result();
        $packages = $this->package($repo, $branches);
        echo json_encode($packages);
        die();
    }

    function repository($repositories)
    {
        $providers = ["providers" => []];
        foreach($repositories as $repo) {
            $branches = new Branch();
            $branches = $branches->where("author", $repo["author"])->where("repo", $repo["repo"])->result();
            $packages = $this->package($repo, $branches);
            $providers["providers"][$repo.""] = ["sha256" => hash("sha256", json_encode($packages))];
        }
        return $providers;
    }

    function package($repository, $branches)
    {
        $packages = ["packages" => []];
        foreach ($branches as $branch) {
            $packages["packages"][$repository.""] = [
                $branch."" => [
                    "name" => $repository."",
                    "description" => "",
                    "uid" => 1,
                    "version" => $branch.""
                ]
            ];
        }
        return $packages;
    }
}