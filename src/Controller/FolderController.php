<?php

namespace App\Controller;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use App\Criteria\Children;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use http\QueryString;

class FolderController
{

    /** @var \eZ\Publish\API\Repository\SearchService */
    protected $searchService;

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    protected $configResolver;

    /** @var \App\Criteria\Children */
    protected $childrenCriteria;

    /**
     * @param \eZ\Publish\API\Repository\SearchService $searchService
     * @param \eZ\Publish\Core\MVC\ConfigResolverInterface $configResolver
     * @param \App\Criteria\Children $childrenCriteria
     */
    public function __construct(
        SearchService $searchService,
        ConfigResolverInterface $configResolver,
        Children $childrenCriteria
    ) {
        $this->searchService = $searchService;
        $this->configResolver = $configResolver;
        $this->childrenCriteria = $childrenCriteria;
    }

    /**
     * Displays blog posts and gallery images on home page.
     *
     * @param \eZ\Publish\Core\MVC\Symfony\View\ContentView $view
     *
     * @return \eZ\Publish\Core\MVC\Symfony\View\ContentView
     */

    public function showAction(ContentView $view)
    {
        $view->addParameters([
            'items' => $this->fetchItems($view->getLocation(), 25),
        ]);
        return $view;
    }

    public function homeAction(ContentView $view)
    {
        $view->addParameters([
            'data' => $this->fetchItems_home($view->getLocation(),25),
        ]);
        return $view;
    }

    private function fetchItems($location, $limit)
    {
        $languages = $this->configResolver->getParameter('languages');
        $query = new Query([]);
        $query->query = $this->childrenCriteria->generateChildCriterion($location, $languages);
        $query->limit = $limit;
        $results = $this->searchService->findContent($query);
        $items = [];
        foreach ($results->searchHits as $item) {
            $items[] = $item->valueObject;
        }
        return $items;
    }

    private function fetchItems_home($location, $limit)
    {
        $query = new Query([
            'filter' => new Criterion\LogicalAnd([
                new Criterion\Visibility(Criterion\Visibility::VISIBLE),
                new Criterion\ContentTypeId(1)
            ])
        ]);
        $languages = $this->configResolver->getParameter('languages');
        $query->query = $this->childrenCriteria->generateChildCriterion($location, $languages);
        $query->limit = $limit;
        $results = $this->searchService->findContent($query);
        $items = [];
        foreach ($results->searchHits as $item) {
            $items[] = $item->valueObject;
        }
        return $items;
    }

}