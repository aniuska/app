<?php
/**
 * Class definition for Wikia\Search\QueryService\Select\Categories
 */
namespace Wikia\Search\QueryService\Select;
use \Solarium_Query_Select, \Wikia\Search\Utilities, \Wikia\CategoryGalleries\services\CategoryService;
/**
 * This class is responsible for performing categories search queries.
 * @author aniuska
 * @package Search
 * @subpackage QueryService
 */
class Category extends OnWiki
{
		
        
        /**
	 * Limit grouped results, in query result.
	 * @var int
	 */
        const GROUP_RESULTS_COUNT = 999; 
        
	/**
	 * Used for tracking
	 * @var string
	 */
	protected $searchType = 'cat';
		
	/**
	 * Identifies a match by domain via mw service. Registers with config and returns if found.
	 * @see \Wikia\Search\QueryService\Select\AbstractSelect::extractMatch()
	 * @return Wikia\Search\Match\Category|null
	 */
	public function extractMatch() {
		
		$match =  $this->service->getCategoryMatchForTermAndNamespaces( $this->config->getQuery()->getSanitizedQuery(),  array( NS_CATEGORY ) );
		if (! empty( $match ) ) {
			$this->config->setCategoryMatch( $match );
		}
		return $this->config->getMatch();
	}
	
	/**
	 * Registers grouping, query parameters, and filters.
	 * @see \Wikia\Search\QueryService\Select\AbstractSelect::registerComponents()
	 * @param Solarium_Query_Select $query
	 * @return Category
	 */
	protected function registerComponents( Solarium_Query_Select $query ) {
		return $this->configureQueryFields()
		            ->registerQueryParams   ( $query )
		            ->registerFilterQueries ( $query )
                            
		;
	}
	
	/**
	 * Registers a filter query for documents matching the category ID of a match, if available.
	 * @see \Wikia\Search\QueryService\Select\AbstractSelect::registerFilterQueryForMatch()
	 * @return Wikia\Search\QueryService\Select\Category
	 */
	protected function registerFilterQueryForMatch() {
		if ( $this->config->hasCategoryMatch() ) {
			$noPtt = Utilities::valueForField( 'id', $this->config->getCategoryMatch()->getResult()->getVar( 'id' ), array( 'negate' => true ) );
			$this->config->setFilterQuery( $noPtt, 'cat' );
		}
		return $this;
	}
	

	/**
	 * Adds wikititle to query fields before querying.
	 * @return \Wikia\Search\QueryService\Select\Category
	 */
	protected function configureQueryFields() {
		$this->config->setQueryField( 'categories', 7 );
		return $this;
	}
	
	/**
	 * Builds the string used with filter queries based on search config
	 * @return string
	 */
	protected function getFilterQueryString() {
                $pageid = $this->config->getCategoryMatch()->getResult()->getVar( 'id' ); 
		$filterQueries = array( Utilities::valueForField( 'pageid', $pageid) );
		
		return $filterQueries;
	}
	
		        
	/**
	 * Return a string of query fields based on configuration
	 * @return string
	 */
	protected function getQueryFieldsString() {
		$queryFieldsString = '';
                
                $queryFieldsToBoosts = $this->config->getQueryFieldsToBoosts();
                $queryFieldsString .= sprintf( '%s^%s ', Utilities::field( 'categories' ), $queryFieldsToBoosts['categories'] );
                
                
		return trim( $queryFieldsString );
	}
	
}

