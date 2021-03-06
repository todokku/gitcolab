<?php

/**
 * This file is part of Gitcolab.
 *
 * (c) Mbechezi mlanawo <mlanawo.mbechezi@kemeter.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gitcolab\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Gitcolab\Git\Repository;

class Project
{
    use TimesheetTrait;

    const SLUG_PATTERN = "[\w\/]+";
    const DEFAULT_BRANCH = 'master';

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $slug;

    /**
     * @var string
     */
    protected $description;

    /**
     *
     * @var \DateTime
     */
    protected $lastActivity;

    /**
     * @var object
     */
    protected $repository;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var object
     */
    protected $organization;

    /**
     * @var string
     */
    protected $defaultBranch;

    /**
     * @var int
     */
    protected $repositorySize;

    /**
     * @var  ArrayCollection
     */
    protected $members;

    /**
     * @var ArrayCollection
     */
    protected $pullRequests;

    /**
     * @var ArrayCollection
     */
    protected $issues;

    /**
     * @var ArrayCollection
     */
    protected $tickets;

    /**
     * @var ArrayCollection
     */
    protected $milestones;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTime();
        $this->pullRequests = new ArrayCollection();
        $this->issues = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param $slug
     * @return self
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    public function getFullSlug()
    {
        return $this->getOrganization()->getSlug() .'/'. $this->getSlug();
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param $description
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @param \DateTime $lastActivity
     * @return self
     */
    public function setLastActivity(\DateTime $lastActivity)
    {
        $this->lastActivity = $lastActivity;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastActivity()
    {
        return $this->lastActivity;
    }

    /**
     * @return string
     */
    public function getDefaultBranch()
    {
        return $this->defaultBranch;
    }

    /**
     * @param string $defaultBranch
     * @return self
     */
    public function setDefaultBranch($defaultBranch)
    {
        $this->defaultBranch = $defaultBranch;

        return $this;
    }

    public function setOrganization(Organization $organization)
    {
        $this->owner = $organization;
        return $this;
    }

    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setRepositoryName($name)
    {
        $this->repository = $name;

        return $this;
    }

    /**
     * @param Repository $repository
     * @return self
     */
    public function setRepository(Repository $repository)
    {
        $this->repository = $repository;
        return $this;
    }

    public function getRepository()
    {
        return $this->repository;
    }


    public function getRepositorySize()
    {
        return $this->repositorySize;
    }

    public function setRepositorySize($repositorySize)
    {
        $this->repositorySize = $repositorySize;

        return $this;
    }

    public function getUsers()
    {
        return [];
    }

    public function getPullRequests()
    {
        return $this->pullRequests;
    }

    public function setPullRequests($pullRequests)
    {
        $this->pullRequests = $pullRequests;


        return $this;
    }

    public function getIssues()
    {
        return $this->tickets;
    }

    /**
     * @return ArrayCollection
     */
    public function getTickets()
    {
        return $this->tickets;
    }

    /**
     * @param ArrayCollection $tickets
     */
    public function setTickets($tickets)
    {
        $this->tickets = $tickets;
    }

    /**
     * @return ArrayCollection
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * @param ArrayCollection $members
     */
    public function setMembers($members)
    {
        $this->members = $members;
    }

    public function __toString()
    {
        return $this->name;
    }
}
