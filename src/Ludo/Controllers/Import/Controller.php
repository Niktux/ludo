<?php

namespace Ludo\Controllers\Import;

use Ludo\Import\BoardgameGeek as BGG;

class Controller extends \Firenote\Controllers\AbstractController
{
    public function __construct()
    {
    }

    public function onInitialize()
    {
        $this->page->addBreadcrumb('Import', 'import_home');
        $this->page->addCss('/assets/ludo/css/ludo.css');
    }

    public function homeAction()
    {
        return $this->page
            ->setTitle('Home')
            ->render('pages/import/home.twig');
    }
    
    public function searchAction()
    {
        $name = $this->request->get('name');
        $exact = $this->request->request->has('exact');
        
        $api = new BGG\Api();
        $games = $api->search($name, $exact);
        
        return $this->page
            ->setTitle('Search "' . $name . '"')
            ->render('pages/import/search.twig', array(
                'searchedName' => $name,
                'games' => $games,
            ));
    }
    
    public function bggImportAction($gameId)
    {
        $api = new BGG\Api();
        $game = $api->load($gameId);

        $images = array(
            'square'    => BGG\Images::getFormatFromAnother($game['box'], BGG\Images::SQUARE),
            'medium'    => BGG\Images::getFormatFromAnother($game['box'], BGG\Images::MEDIUM),
            'thumbnail' => BGG\Images::getFormatFromAnother($game['box'], BGG\Images::THUMBNAIL),
        );
        
        return $this->page
            ->addBreadcrumb('Boargamegeek', 'import_home')
            ->setTitle($game['name'])
            ->render('pages/import/bgg.twig', array(
                'game' => $game,
                'images' => $images,
        ));
    }
}