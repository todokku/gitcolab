<?php

/**
 * This file is part of Gitcolab.
 *
 * (c) Mbechezi mlanawo <mlanawo@mbechezi.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gitcolab\Bundle\AppBundle\Controller;

use Gitonomy\Git\Repository;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use Michelf\Markdown;
use Gitcolab\Bundle\AppBundle\Form\Type\ProjectType;
use Gitcolab\Bundle\AppBundle\Model\Project;
use Gitcolab\Bundle\AppBundle\GitcolabEvents;
use Gitcolab\Bundle\AppBundle\Event\ProjectEvent;
use Gitonomy\Git\Blob;
use Gitonomy\Git\Reference;
use Gitonomy\Git\Tree;

class ProjectController extends Controller
{
    public function createAction(Request $request)
    {
        $project = new Project();

        $form = $this->createForm(new ProjectType(), $project, array(
            'user_id' => $this->getUser()
        ));

        if ($form->handleRequest($request)->isValid()) {

            $this->persistAndFlush($project);
            $project->addUser($this->getUser(), 'ROLE_ADMIN');
            $this->persistAndFlush($project);

            $this->dispatch(GitcolabEvents::PROJECT_CREATE, new ProjectEvent($project, $this->getUser()));

            return $this->redirectToRoute('dashboard');
        }

        $view = View::create();
        $view->setData(array(
            'form' => $form->createView()
        ));
        $view->setTemplate('GitcolabAppBundle:Project:create.html.twig');

        return $this->handleView($view);
    }

    public function showAction(Request $request, $slug)
    {
        $view = View::create();

        $slugParameter = explode('/', $slug);
        $project = $this->getRepository('Project')->findProject($slugParameter[0], $slugParameter[1]);

        if (!$project) {
            throw $this->createNotFoundException();
        }

        $data = array(
            'project' => $project,
            'slug' => $slug,
            'gitcolab_url' => str_replace('http://', '', $this->container->getParameter('gitcolab.url'))
        );
        $path = '';

        $repository = $project->getRepository();
        $refs       = $repository->getReferences();

        $revision = $repository->getRevision($project->getDefaultBranch());

        try {
            $commit = $revision->getCommit();
            $tree = $commit->getTree();
            if (strlen($path) > 0 && '/' === substr($path, 0, 1)) {
                $path = substr($path, 1);
            }

            try {
                $element = $tree->resolvePath($path);
            } catch (\InvalidArgumentException $e) {
                throw $this->createNotFoundException($e->getMessage());
            }

            $data = array_merge($data, array(
                'tree' => $tree,
                'revision' => $revision,
                'path' => $path,
                'refs' => $refs,
                'readme' => $this->getReadme($repository, $tree, $path)
            ));



        } catch (\InvalidArgumentException $e) {
            $data['revision'] =  false;
        }

        $view
            ->setData($data)
            ->setTemplate('GitcolabAppBundle:Project:show.html.twig');

        return $this->handleView($view);
    }

    /**
     * @param Repository $repository
     * @param $tree
     * @param $path
     * @return array|null
     */
    public function getReadme(Repository $repository, $tree, $path)
    {
        foreach ($tree->getEntries() as $name =>  $file) {

            if (preg_match('/^readme*/i', $name)) {
                return array(
                    'filename' => $name,
                    'content'  => Markdown::defaultTransform($tree->resolvePath($name)->getContent())
                );
            }
        }
        return null;
    }

    /**
     * @param $repository
     * @param $revision Can be a branch name or a commit hash
     * @param $path
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function treeAction($repository, $revision, $path)
    {
        $project    = $this->getProject($repository);
        $repository = $project->getRepository();
        $refs       = $repository->getReferences();
        if ($refs->hasBranch($revision)) {
            $revision = $refs->getBranch($revision);
        } else {
            $revision = $repository->getRevision($revision);
        }
        $commit = $revision->getCommit();
        $tree = $commit->getTree();

        if (strlen($path) > 0 && '/' === substr($path, 0, 1)) {
            $path = substr($path, 1);
        }

        try {
            $element = $tree->resolvePath($path);
        } catch (\InvalidArgumentException $e) {
            throw $this->createNotFoundException($e->getMessage());
        }

        $parameters = array(
            'project'  => $project,
            'revision' => $revision,
            'path'     => $path,
        );

        if ($element instanceof Blob) {
            $parameters['blob'] = $element;
            $tpl = 'GitcolabAppBundle:Project:blob.html.twig';
        } elseif ($element instanceof Tree) {
            $parameters['tree'] = $element;
            $tpl = 'GitcolabAppBundle:Project:tree.html.twig';
        }

        return $this->render($tpl, $parameters);
    }

    /**
     * @param $slug
     * @return mixed
     */
    public function getProject($slug)
    {
        $slugParameter = explode('/', $slug);
        $project = $this->getRepository('Project')->findProject($slugParameter[0], $slugParameter[1]);

        return $project;
    }
}
