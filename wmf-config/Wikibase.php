<?php

require_once( "$IP/extensions/Wikidata/Wikidata.php" );

if ( $wmgUseWikibaseRepo ) {
	if ( $wgDBname === 'testwikidatawiki' ) {
		$wgCacheEpoch = '20140814215741';
	} else {
		// @todo: can move to InitialiseSettings later, but having here
		$wgCacheEpoch = '20140429173328';
	}

	$baseNs = 120;

	// Define the namespace indexes
	define( 'WB_NS_PROPERTY', $baseNs );
	define( 'WB_NS_PROPERTY_TALK', $baseNs + 1 );
	define( 'WB_NS_QUERY', $baseNs + 2 );
	define( 'WB_NS_QUERY_TALK', $baseNs + 3 );

	$wgNamespaceAliases['Item'] = NS_MAIN;
	$wgNamespaceAliases['Item_talk'] = NS_TALK;

	// Define the namespaces
	$wgExtraNamespaces[WB_NS_PROPERTY] = 'Property';
	$wgExtraNamespaces[WB_NS_PROPERTY_TALK] = 'Property_talk';
	$wgExtraNamespaces[WB_NS_QUERY] = 'Query';
	$wgExtraNamespaces[WB_NS_QUERY_TALK] = 'Query_talk';

	$wgWBRepoSettings['dataSquidMaxage'] = 1 * 60 * 60;
	$wgWBRepoSettings['sharedCacheDuration'] = 60 * 60 * 24;

	// Assigning the correct content models to the namespaces
	$wgWBRepoSettings['entityNamespaces'][CONTENT_MODEL_WIKIBASE_ITEM] = NS_MAIN;
	$wgWBRepoSettings['entityNamespaces'][CONTENT_MODEL_WIKIBASE_PROPERTY] = WB_NS_PROPERTY;

	$wgWBRepoSettings['normalizeItemByTitlePageNames'] = true;

	$wgWBRepoSettings['dataRightsText'] = 'Creative Commons CC0 License';
	$wgWBRepoSettings['dataRightsUrl'] = 'https://creativecommons.org/publicdomain/zero/1.0/';

	$wgWBRepoSettings['siteLinkGroups'] = array(
		'wikipedia',
		'wikiquote',
		'wikisource',
		'wikivoyage',
		'special'
	);

	$wgWBRepoSettings['specialSiteLinkGroups'] = array( 'commons' );

	if ( $wgDBname === 'testwikidatawiki' ) {
		$wgWBRepoSettings['specialSiteLinkGroups'][] = 'testwikidata';
	} else {
		$wgWBRepoSettings['specialSiteLinkGroups'][] = 'wikidata';
	}

	if ( $wgDBname === 'testwikidatawiki' ) {
		$wgWBRepoSettings['badgeItems'] = array(
			'Q608' => 'wb-badge-goodarticle',
			'Q609' => 'wb-badge-featuredarticle'
		);
	} else {
		$wgWBRepoSettings['badgeItems'] = array(
			'Q17437798' => 'wb-badge-goodarticle',
			'Q17437796' => 'wb-badge-featuredarticle'
		);
	}

	if ( $wgDBname === 'testwikidatawiki' ) {
		// there is no cronjob dispatcher yet, this will do nothing
		$wgWBRepoSettings['clientDbList'] = array( 'test2wiki' );
	} else {
		$wgWBRepoSettings['clientDbList'] = array_map(
			'trim',
			file( getRealmSpecificFilename( "$IP/../wikidataclient.dblist" ) )
		);
	}

	$wgWBRepoSettings['localClientDatabases'] = array_combine(
		$wgWBRepoSettings['clientDbList'],
		$wgWBRepoSettings['clientDbList']
	);

	// Bug 51637 and 46953
	$wgGroupPermissions['*']['property-create'] = ( $wgDBname === 'testwikidatawiki' );

	$wgWBRepoSettings['internalEntitySerializerClass'] = 'Wikibase\Lib\Serializers\LegacyInternalEntitySerializer';

	$wgWBRepoSettings['sharedCacheKeyPrefix'] = "$wmgWikibaseCachePrefix/WBL-$wmfVersionNumber";

	$wgPropertySuggesterMinProbability = 0.071;
}

if ( $wmgUseWikibaseClient ) {

	// to be safe, keeping this here although $wgDBname is default setting
	$wgWBClientSettings['siteGlobalID'] = $wgDBname;

	if ( in_array( $wgDBname, array( 'test2wiki', 'testwiki', 'testwikidatawiki' ) ) ) {
		$wgWBClientSettings['changesDatabase'] = 'testwikidatawiki';
		$wgWBClientSettings['repoDatabase'] = 'testwikidatawiki';
		$wgWBClientSettings['repoUrl'] = "//test.wikidata.org";
	} else {
		$wgWBClientSettings['changesDatabase'] = 'wikidatawiki';
		$wgWBClientSettings['repoDatabase'] = 'wikidatawiki';
		$wgWBClientSettings['repoUrl'] = "//{$wmfHostnames['wikidata']}";
	}

	$wgWBClientSettings['repoNamespaces'] = array(
		'wikibase-item' => '',
		'wikibase-property' => 'Property'
	);

	$wgWBRepoSettings['siteLinkGroups'] = array(
		'wikipedia',
		'wikiquote',
		'wikisource',
		'wikivoyage',
		'special'
	);

	$wgWBRepoSettings['specialSiteLinkGroups'] = array( 'commons' );

	if ( $wgDBname !== 'testwikidatawiki' ) {
		$wgWBRepoSettings['specialSiteLinkGroups'][] = 'testwikidata';
	} elseif ( $wgDBname === 'wikidatawiki' ) {
		$wgWBRepoSettings['specialSiteLinkGroups'][] = 'wikidata';
	}

	if ( $wgDBname === 'commonswiki' ) {
		$wgWBClientSettings['languageLinkSiteGroup'] = 'wikipedia';
	}

	$wgWBClientSettings['siteGroup'] = $wmgWikibaseSiteGroup;

	$wgHooks['SetupAfterCache'][] = 'wmfWBClientExcludeNS';

	function wmfWBClientExcludeNS() {
		global $wgWBClientSettings;

		$wgWBClientSettings['excludeNamespaces'] = array_merge(
			MWNamespace::getTalkNamespaces(),
			// 1198 => NS_TRANSLATE
			array( NS_USER, NS_FILE, NS_MEDIAWIKI, 1198 )
		);

		return true;
	};

	if ( $wgDBname === 'testwikidatawiki' ) {
		$wgWBClientSettings['namespaces'] = array(
			NS_PROJECT,
			NS_TEMPLATE,
			NS_HELP
		);

		$wgWBClientSettings['languageLinkSiteGroup'] = 'wikipedia';
		$wgWBClientSettings['injectRecentChanges'] = false;
		$wgWBClientSettings['showExternalRecentChanges'] = false;
	}

	if ( $wgDBname === 'testwikidatawiki' ) {
		$wgWBClientSettings['allowArbitraryDataAccess'] = true;
	}

	$wgWBClientSettings['allowArbitraryDataAccess'] = false;

	$wgWBClientSettings['sharedCacheDuration'] = 60 * 60 * 24;

	foreach( $wmgWikibaseClientSettings as $setting => $value ) {
		$wgWBClientSettings[$setting] = $value;
	}

	$wgWBClientSettings['allowDataTransclusion'] = $wmgWikibaseEnableData;
	$wgWBClientSettings['sharedCacheKeyPrefix'] = "$wmgWikibaseCachePrefix/WBL-$wmfVersionNumber";
}
