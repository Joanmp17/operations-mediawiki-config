<?php
/* vim: set sw=4 ts=4 noet foldmarker=@{,@} foldmethod=marker: */

# WARNING: This file is publically viewable on the web. Do not put private data here.

# This file hold the CirrusSearch configuration which is common to all realms,
# ie settings should apply to both the production cluster and the beta
# cluster.
# If you ever want to stick there an IP address, you should use the per realm
# specific files CirrusSearch-labs.php and CirrusSearch-production.php

# See: https://wikitech.wikimedia.org/wiki/Search
#
# Contact Wikimedia operations or platform engineering for more details.

$wgSearchType = 'CirrusSearch';

if ( $wmgUseClusterJobqueue ) {
	# The secondary update job has a delay of a few seconds to make sure that Elasticsearch
	# has completed a refresh cycle between when the data that the job needs is added and
	# when the job is run.
	$wgJobTypeConf['cirrusSearchIncomingLinkCount'] = array( 'checkDelay' => true ) +
		$wgJobTypeConf['default'];
}

# Set up the the default cluster to send queries to,
# and the list of clusters to write to.
if ( $wmgCirrusSearchDefaultCluster === 'local' ) {
	$wgCirrusSearchDefaultCluster = $wmfDatacenter;
} else {
	$wgCirrusSearchDefaultCluster = $wmgCirrusSearchDefaultCluster;
}
$wgCirrusSearchWriteClusters = $wmgCirrusSearchWriteClusters;

# Enable user testing
$wgCirrusSearchUserTesting = $wmgCirrusSearchUserTesting;

# Turn off leading wildcard matches, they are a very slow and inefficient query
$wgCirrusSearchAllowLeadingWildcard = false;

# Turn off the more accurate but slower search mode.  It is most helpful when you
# have many small shards.  We don't do that in production and we could use the speed.
$wgCirrusSearchMoreAccurateScoringMode = false;

# Raise the refresh interval to save some CPU at the cost of being slightly less realtime.
$wgCirrusSearchRefreshInterval = 30;

# Limit the number of states generated by wildcard queries (500 will allow about 20 wildcards)
$wgCirrusSearchQueryStringMaxDeterminizedStates = 500;

# Lower the regex timeouts - the defaults are too high in an environment with reverse proxies.
$wgCirrusSearchSearchShardTimeout[ 'regex' ] = '40s';
$wgCirrusSearchClientSideSearchTimeout[ 'regex' ] = 80;

# Set the backoff for Cirrus' job that reacts to template changes - slow and steady
# will help prevent spikes in Elasticsearch load.
// $wgJobBackoffThrottling['cirrusSearchLinksUpdate'] = 5;  -- disabled, Ori 3-Dec-2015
# Also engage a delay for the Cirrus job that counts incoming links to pages when
# pages are newly linked or unlinked.  Too many link count queries at once could flood
# Elasticsearch.
// $wgJobBackoffThrottling['cirrusSearchIncomingLinkCount'] = 1; -- disabled, Ori 3-Dec-2015

# Ban the hebrew plugin, it is unstable
$wgCirrusSearchBannedPlugins[] = 'elasticsearch-analysis-hebrew';

# Build and use an ngram index for faster regex matching
$wgCirrusSearchWikimediaExtraPlugin = array(
	'regex' => array(
		'build',
		'use',
	),
	'super_detect_noop' => true,
	'field_value_factor_with_default' => true,
	'id_hash_mod_filter' => true,
);

# Enable the "experimental" highlighter on all wikis
$wgCirrusSearchUseExperimentalHighlighter = true;
$wgCirrusSearchOptimizeIndexForExperimentalHighlighter = true;

# Setup the feedback link on Special:Search if enabled
$wgCirrusSearchFeedbackLink = $wmgCirrusSearchFeedbackLink;

# Settings customized per index.
$wgCirrusSearchShardCount = $wmgCirrusSearchShardCount;
$wgCirrusSearchReplicas = $wmgCirrusSearchReplicas;
$wgCirrusSearchMaxShardsPerNode = $wmgCirrusSearchMaxShardsPerNode;
$wgCirrusSearchPreferRecentDefaultDecayPortion = $wmgCirrusSearchPreferRecentDefaultDecayPortion;
$wgCirrusSearchBoostLinks = $wmgCirrusSearchBoostLinks;
$wgCirrusSearchWeights = array_merge( $wgCirrusSearchWeights, $wmgCirrusSearchWeightsOverrides );
$wgCirrusSearchPowerSpecialRandom = $wmgCirrusSearchPowerSpecialRandom;
$wgCirrusSearchAllFields = $wmgCirrusSearchAllFields;
$wgCirrusSearchNamespaceWeights = $wmgCirrusSearchNamespaceWeightOverrides +
	$wgCirrusSearchNamespaceWeights;

// We had an incident of filling up the entire clusters redis instances after
// 6 hours, half of that seems reasonable.
$wgCirrusSearchDropDelayedJobsAfter = 60 * 60 * 3;

// Enable cache warming for wikis with more than one shard.  Cache warming is good
// for smoothing out I/O spikes caused by merges at the cost of potentially polluting
// the cache by adding things that won't be used.

// Wikis with more then one shard or with multi-cluster configuration is a
// decent way of saying "wikis we expect will get some search traffic every
// few seconds".  In this commonet the term "cache" refers to all kinds of
// caches: the linux disk cache, Elasticsearch's filter cache, whatever.
if ( isset( $wgCirrusSearchShardCount['eqiad'] ) ) {
	$wgCirrusSearchMainPageCacheWarmer = true;
} else {
	$wgCirrusSearchMainPageCacheWarmer = ( $wgCirrusSearchShardCount['content'] > 1 );
}

// Enable concurrent search limits for specified abusive networks
$wgCirrusSearchForcePerUserPoolCounter = $wmgCirrusSearchForcePerUserPoolCounter;

// Commons is special
if ( $wgDBname == 'commonswiki' ) {
	$wgCirrusSearchNamespaceMappings[ NS_FILE ] = 'file';
	$wgCirrusSearchReplicaCount['file'] = 2;
} elseif ( $wgDBname == 'officewiki' || $wgDBname == 'foundationwiki' ) {
	// T94856 - makes searching difficult for locally uploaded files
	// T76957 - doesn't make sense to have Commons files on foundationwiki search
} else { // So is everyone else, for using commons
	$wgCirrusSearchExtraIndexes[ NS_FILE ] = array( 'commonswiki_file' );
}

// Configuration for initial test deployment of inline interwiki search via
// language detection on the search terms. With EnableAltLanguage set to false
// this is only available with a special query string (cirrusAltLanguage=yes)
$wgCirrusSearchEnableAltLanguage = $wmgCirrusSearchEnableAltLanguage;
$wgCirrusSearchInterwikiProv = 'iwsw1';

$wgCirrusSearchWikiToNameMap = $wmgCirrusSearchWikiToNameMap;
$wgCirrusSearchLanguageToWikiMap = $wmgCirrusSearchLanguageToWikiMap;

$wgHooks['CirrusSearchMappingConfig'][] = function( array &$config, $mappingConfigBuilder ) {
	$config['page']['properties']['popularity_score'] = array(
		'type' => 'double',
	);
};

// Set the scoring method
$wgCirrusSearchCompletionDefaultScore = 'popqual';

// PoolCounter needs to be adjusted to account for additional latency when default search
// is pointed at a remote datacenter. Currently this makes the assumption that it will either
// be eqiad or codfw which have ~40ms latency between them. Multiples are chosen using
// (p75 + cross dc latency)/p75
if ( $wgCirrusSearchDefaultCluster !== $wmfDatacenter ) {
	// prefix has p75 of ~30ms
	if ( isset( $wgPoolCounterConf[ 'CirrusSearch-Prefix' ] ) ) {
		$wgPoolCounterConf['CirrusSearch-Prefix']['workers'] *= 2;
	}
	// namespace has a p75 of ~15ms
	if ( isset( $wgPoolCounterConf['CirrusSearch-NamespaceLookup' ] ) ) {
		$wgPoolCounterConf['CirrusSearch-NamespaceLookup']['workers'] *= 3;
	}
	// completion has p75 of ~30ms
	if ( isset( $wgPoolCounterConf['CirrusSearch-Completion'] ) ) {
		$wgPoolCounterConf['CirrusSearch-Completion'] *= 2;
	}
}

// Enable completion suggester
$wgCirrusSearchUseCompletionSuggester = $wmgCirrusSearchUseCompletionSuggester;

// Configure ICU Folding
$wgCirrusSearchUseIcuFolding = $wmgCirrusSearchUseIcuFolding;

// List of extra rescore profiles @{cirrus extra rescore profiles
// These profiles are needed to run optimization plans with large
// sample of queries in production.
$wgCirrusSearchRescoreProfiles += array(
    'geomean_log' => array(
        'supported_namespaces' => 'content',
        'fallback_profile' => 'default',
        'rescore' => array(
            array(
                'window' => 8192,
                'window_size_override' => 'CirrusSearchFunctionRescoreWindowSize',
                'type' => 'function_score',
                'function_chain' => 'geomean_log',
                'query_weight' => 1.0,
                'rescore_query_weight' => 1.0,
                'score_mode' => 'multiply',
            ),
            array(
                'window' => 8192,
                'window_size_override' => 'CirrusSearchFunctionRescoreWindowSize',
                'type' => 'function_score',
                'function_chain' => 'optional_chain',
                'score_mode' => 'multiply',
            ),
        ),
    ),
    'geomean_satu' => array(
        'supported_namespaces' => 'content',
        'fallback_profile' => 'default',
        'rescore' => array(
            array(
                'window' => 8192,
                'window_size_override' => 'CirrusSearchFunctionRescoreWindowSize',
                'type' => 'function_score',
                'function_chain' => 'geomean_satu',
                'query_weight' => 1.0,
                'rescore_query_weight' => 1.0,
                'score_mode' => 'multiply',
            ),
            array(
                'window' => 8192,
                'window_size_override' => 'CirrusSearchFunctionRescoreWindowSize',
                'type' => 'function_score',
                'function_chain' => 'optional_chain',
                'score_mode' => 'multiply',
            ),
        ),
    ),
);


$wgCirrusSearchRescoreFunctionScoreChains += array(
	// GeoMean with logscale_boost
	'geomean_log' => array(
		'functions' => array(
			array(
				'type' => 'geomean',
				'params' => array(
					'impact' => array(
						'uri_param_override' => 'cirrusGeoMeanLogImpact',
						'config_override' => 'CirrusSearchGeoMeanLogImpact',
						'value' => 1,
					),
					'members' => array(
						array(
							'weight' => array(
								'uri_param_override' => 'cirrusBoostLinksWeight',
								'config_override' => 'CirrusSearchBoostLinksWeight',
								'value' => 1,
							),
							'type' => 'logscale_boost',
							'params' => array(
								'field' => 'incoming_links',
								'scale' => array(
									'value' => 500000,
									'uri_param_override' => 'cirrusBoostLinksScale',
									'config_override' => 'CirrusSearchBoostLinksScale',
								),
								'midpoint' => array(
									'value' => 1000,
									'uri_param_override' => 'cirrusBoostLinksCenter',
									'config_override' => 'CirrusSearchBoostLinksCenter',
								),
							),
						),
						array(
							'weight' => array(
								'uri_param_override' => 'cirrusPopScoreWeight',
								'config_override' => 'CirrusSearchPopScoreWeight',
								'value' => 0,
							),
							'type' => 'logscale_boost',
							'params' => array(
								'field' => 'popularity_score',
								'scale' => array(
									'value' => 0.0001,
									'uri_param_override' => 'cirrusPopScoreScale',
									'config_override' => 'CirrusSearchPopScoreScale',
								),
								'midpoint' => array(
									'value' => 0.000007,
									'uri_param_override' => 'cirrusPopScoreCenter',
									'config_override' => 'CirrusSearchPopScoreCenter',
								),
							),
						),
						array(
							'weight' => array(
								'uri_param_override' => 'cirrusBoostSizeWeight',
								'config_override' => 'CirrusSearchBoostSizeWeight',
								'value' => 0,
							),
							'type' => 'logscale_boost',
							'params' => array(
								'field' => 'text.word_count',
								'scale' => array(
									'value' => 30000,
									'uri_param_override' => 'cirrusBoostSizeScale',
									'config_override' => 'CirrusSearchBoostSizeScale',
								),
								'midpoint' => array(
									'value' => 350,
									'uri_param_override' => 'cirrusBoostSizeCenter',
									'config_override' => 'CirrusSearchBoostSizeCenter',
								),
							),
						),
					),
				),
			),
		),
	),
	// GeoMean with saturation function
	'geomean_satu' => array(
		'functions' => array(
			array(
				'type' => 'geomean',
				'params' => array(
					'impact' => array(
						'uri_param_override' => 'cirrusGeoMeanSatuImpact',
						'config_override' => 'CirrusSearchGeoMeanSatuImpact',
						'value' => 0.5,
					),
					'members' => array(
						array(
							'weight' => array(
								'uri_param_override' => 'cirrusBoostLinksWeight',
								'config_override' => 'CirrusSearchBoostLinksWeight',
								'value' => 1,
							),
							'type' => 'satu',
							'params' => array(
								'field' => 'incoming_links',
								'k' => array(
									'value' => 300,
									'uri_param_override' => 'cirrusBoostLinksK',
									'config_override' => 'CirrusSearchBoostLinksK',
								),
								'a' => array(
									'value' => 250,
									'uri_param_override' => 'cirrusBoostLinksA',
									'config_override' => 'CirrusSearchBoostLinksA',
								),
							),
						),
						array(
							'weight' => array(
								'uri_param_override' => 'cirrusPopScoreWeight',
								'config_override' => 'CirrusSearchPopScoreWeight',
								'value' => 0,
							),
							'type' => 'satu',
							'params' => array(
								'field' => 'popularity_score',
								'k' => array(
									'value' => 0.000007,
									'uri_param_override' => 'cirrusPopScoreK',
									'config_override' => 'CirrusSearchPopScoreK',
								),
								'a' => array(
									'value' => 250,
									'uri_param_override' => 'cirrusPopScoreA',
									'config_override' => 'CirrusSearchPopScoreA',
								),
							),
						),
						array(
							'weight' => array(
								'uri_param_override' => 'cirrusBoostSizeWeight',
								'config_override' => 'CirrusSearchBoostSizeWeight',
								'value' => 0,
							),
							'type' => 'satu',
							'params' => array(
								'field' => 'text.word_count',
								'k' => array(
									'value' => 300,
									'uri_param_override' => 'cirrusBoostSizeK',
									'config_override' => 'CirrusSearchBoostSizeK',
								),
								'a' => array(
									'value' => 1,
									'uri_param_override' => 'cirrusBoostSizeA',
									'config_override' => 'CirrusSearchBoostSizeA',
								),
							),
						),
					),
				),
			),
		),
	),
);

# TODO: move to InitialiseSettings.php if this technique is proven usefull and
# once we have an optimized value for all wikis. (Default values are an
# approximation for enwiki)
# (All values need to be overridden here for runSearch to work)
$wgCirrusSearchGeoMeanLogImpact = 1;
$wgCirrusSearchGeoMeanSatuImpact = 0.5;
$wgCirrusSearchBoostLinksWeight = 1;
$wgCirrusSearchPopScoreWeight = 0;
$wgCirrusSearchBoostSizeWeight = 0;

$wgCirrusSearchBoostLinksScale = 500000;
$wgCirrusSearchBoostLinksCenter = 1000;
$wgCirrusSearchPopScoreScale = 0.0001;
$wgCirrusSearchPopScoreCenter = 0.000003;
$wgCirrusSearchBoostSizeScale = 30000;
$wgCirrusSearchBoostSizeCenter = 350;

$wgCirrusSearchBoostLinksK = 1000;
$wgCirrusSearchPopScoreK = 0.000007;
$wgCirrusSearchBoostSizeK = 350;
$wgCirrusSearchBoostLinksA = 1;
$wgCirrusSearchPopScoreA = 1;
$wgCirrusSearchBoostSizeA = 1;

// @} end of cirrus extra rescore profiles


# Load per realm specific configuration, either:
# - CirrusSearch-labs.php
# - CirrusSearch-production.php
#
require "{$wmfConfigDir}/CirrusSearch-{$wmfRealm}.php";
