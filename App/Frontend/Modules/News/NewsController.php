<?php
/**
 * Created by PhpStorm.
 * User: adumontois
 * Date: 03/10/2016
 * Time: 14:48
 */

namespace App\Frontend\Modules\News;

use App\Traits\AppController;
use Entity\Comment;
use Entity\News;
use Entity\User;
use FormBuilder\CommentFormBuilder;
use Model\CommentsManager;
use Model\NewsManager;
use Model\UserManagerPDO;
use OCFram\Application;
use \OCFram\BackController;
use OCFram\FormHandler;
use \OCFram\HTTPRequest;
use OCFram\HTTPResponse;

class NewsController extends BackController {
	use AppController;
	
	/**
	 * NewsController constructor.
	 * Construit un backcontroller en spécifiant la DB news.
	 *
	 * @param Application $App
	 * @param string      $module
	 * @param string      $action
	 */
	public function __construct( Application $App, $module, $action ) {
		parent::__construct( $App, $module, $action, 'news' );
	}
	
	/**
	 * Affiche les $nombre_news dernières news, $nombre_news est une constante déclarée dans le fichier app.xml.
	 */
	public function executeBuildIndex() {
		/**
		 * @var $News_manager NewsManager
		 * @var $Liste_news_a News[]
		 */
		// Récupérer la config
		$nombre_news   = $this->app()->config()->get( 'nombre_news' );
		$longueur_news = $this->app()->config()->get( 'longueur_news' );
		
		// Récupérer le manager des news
		
		$News_manager = $this->managers->getManagerOf();
		
		// Récupérer la liste des news à afficher
		$Liste_news_a = $News_manager->getNewscSortByIdDesc( 0, $nombre_news );
		foreach ( $Liste_news_a as $News ) {
			// Prendre le nombre de caractères nécessaires
			$News->setContent( substr( $News->content(), 0, $longueur_news ) );
			if ( strlen( $News->content() ) == $longueur_news ) {
				$News->setContent( substr( $News->content(), 0, strrpos( $News->content(), ' ' ) ) . '...' );
			}
		}
		$this->page->addVar( 'title', 'Liste des ' . $nombre_news . ' dernières news' );
		$this->page->addVar( 'News_list_a', $Liste_news_a );
		
		$this->run();
	}
	
	/**
	 * @param HTTPRequest $Request
	 * Affiche une news et les commentaires associés
	 */
	public function executeBuildNews( HTTPRequest $Request ) {
		/**
		 * @var $News_manager    NewsManager
		 * @var $News            News
		 * @var $Comment_manager CommentsManager
		 */
		$News_manager = $this->managers->getManagerOf();
		$News         = $News_manager->getNewscUsingNewscId( $Request->getData( 'id' ) );
		$News->format();
		
		// Si la news n'existe pas on redirige vers une erreur 404
		if ( empty( $News ) ) {
			$this->app->httpResponse()->redirectError( HTTPResponse::NOT_FOUND, new \RuntimeException( 'La news demandée n\'existe pas !' ) );
		}
		
		// Afficher les commentaires
		$Comment_manager  = $this->managers->getManagerOf( 'Comments' );
		$Liste_comments_a = $Comment_manager->getCommentcUsingNewscIdSortByIdDesc( $News->id() );
		
		$action_a = null;
		foreach ($Liste_comments_a as $Comment) {
			$Comment->formatDate();
			// Générer les actions dans tous les cas
			$action_a[$Comment->id()] = '- <a href="admin/comment-update-'.$Comment->id().'.html">Modifier</a>
				| <a href="admin/comment-delete-'.$Comment->id().'.html">Supprimer</a>';
		}
		
		if ($this->app->user()->authenticationLevel() == User::USERY_SUPERADMIN) {
			$this->page->addVar( 'action_a', $action_a);
		}
		else {
			// Ne pas intégrer les actions si l'utilisateur n'a pas les droits
			$this->page->addVar( 'action_a', null);
		}
		$this->page->addVar( 'title', $News->title() );
		$this->page->addVar( 'News', $News );
		$this->page->addVar( 'Comment_list_a', $Liste_comments_a );
		$this->page->addVar( 'User', $this->app->user() );
		
		$this->run();
	}
	
	/**
	 * @param HTTPRequest $Request
	 * Insère un commentaire
	 */
	public function executePutInsertComment( HTTPRequest $Request ) {
		/**
		 * @var UserManagerPDO $User_manager
		 * @var User $User
		 */
		if ( $Request->method() == HTTPRequest::POST_METHOD ) {
			$Commentaire = new Comment( array(
				'fk_SNC'    => $Request->getData( 'id' ),
				'author'  => $Request->postData( 'author' ),
				'content' => $Request->postData( 'content' ),
			) );
		}
		else {
			$Commentaire = new Comment();
			// Préremplir le champ auteur si l'utilisateur est connecté
			if ($this->app->user()->isAuthenticated()) {
				$User_manager = $this->managers->getManagerOf('User');
				$User = $User_manager->getUsercUsingUsercId($this->app->user()->userId());
				if (null != $User) {
					$Commentaire->setAuthor($User->login());
				}
			}
		}
		// Construction du formulaire
		// 1) Données values
		$Form_builder = new CommentFormBuilder( $Commentaire );
		// 2) Construction et vérification des données
		$Form_builder->build();
		$Form = $Form_builder->form();
		
		// Sauvegarde avec le handler
		$Form_handler = new FormHandler( $Form, $this->managers->getManagerOf( 'Comments' ), $Request );
		if ( $Form_handler->process() ) {
			$this->app->user()->setFlash( 'Votre commentaire a bien été ajouté.' );
			$this->app->httpResponse()->redirect( 'news-' . $Request->getData( 'id' ) . '.html' );
		}
		$this->page->addVar( 'title', 'Ajout d\'un commentaire' );
		// Passer le formulaire à la vue
		$this->page->addVar( 'form', $Form_builder->form()->createView() );
		
		$this->run();
	}
}