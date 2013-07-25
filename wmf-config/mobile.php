<?php

# WARNING: This file is publically viewable on the web.
# # Do not put private data here.

if ( $wmgMobileFrontend ) {
	require_once( "$IP/extensions/MobileFrontend/MobileFrontend.php" );
	$wgMFNoindexPages = false;
	$wgMFNearby = $wmgMFNearby && $wmgEnableGeoData;
	$wgMFPhotoUploadEndpoint = $wmgMFPhotoUploadEndpoint;
	$wgMFUseCentralAuthToken = $wmgMFUseCentralAuthToken;
	$wgMFPhotoUploadWiki = $wmgMFPhotoUploadWiki;
	$wgMFNearbyNamespace = $wmgMFNearbyNamespace;
	$wgMFPhotoUploadAppendToDesc = $wmgMFPhotoUploadAppendToDesc;

	if ( $wmgMobileFrontendLogo ) {
		$wgMobileFrontendLogo = $wmgMobileFrontendLogo;
	}
	if ( $wmgMFRemovableClasses ) {
		$wgMFRemovableClasses = $wmgMFRemovableClasses;
	}
	if ( $wmgMFCustomLogos ) {
		if ( isset( $wmgMFCustomLogos['copyright'] ) ) {
			$wmgMFCustomLogos['copyright'] = str_replace( '{wgExtensionAssetsPath}', $wgExtensionAssetsPath, $wmgMFCustomLogos['copyright'] );
		}
		$wgMFCustomLogos = $wmgMFCustomLogos;
	}
}


// If a URL template is set for MobileFrontend, use it.
if ( $wmgMobileUrlTemplate ) {
	$wgMobileUrlTemplate = $wmgMobileUrlTemplate;
}

if ( $wmgZeroRatedMobileAccess ) {
	require_once( "$IP/extensions/ZeroRatedMobileAccess/ZeroRatedMobileAccess.php" );
	$wgZeroRatedMobileAccessConfigIndexUri = 'http://meta.wikimedia.org/w/index.php';
}

if ( $wmgZeroDisableImages ) {
	if ( isset( $_SERVER['HTTP_X_SUBDOMAIN'] ) && strtoupper( $_SERVER['HTTP_X_SUBDOMAIN'] ) == 'ZERO' ) {
		$wgZeroDisableImages = $wmgZeroDisableImages;
	}
}

// Enable loading of desktop-specific resources from MobileFrontend
if ( $wmgMFEnableDesktopResources ) {
	$wgMFEnableDesktopResources = true;
}

// Enable appending of TM (text) / (R) (icon) on site name in footer.
$wgMFTrademarkSitename = $wmgMFTrademarkSitename;

// Enable Schemas for event logging (jdlrobson; 07-Feb-2012)
if ( $wmgUseEventLogging ) {
	$wgResourceModules['mobile.watchlist.schema'] = array(
		'class' => 'ResourceLoaderSchemaModule',
		'schema' => 'MobileBetaWatchlist',
		'revision' => 5281061,
		'targets' => 'mobile',
	);

	$wgResourceModules['mobile.uploads.schema'] = array(
		'class' => 'ResourceLoaderSchemaModule',
		'schema' => 'MobileWebUploads',
		'revision' => 5383883,
		'targets' => 'mobile',
	);

	$wgResourceModules['mobile.editing.schema'] = array(
		'class' => 'ResourceLoaderSchemaModule',
		'schema' => 'MobileWebEditing',
		'targets' => 'mobile',
		'revision' => 5644223,
	);

	$wgHooks['EnableMobileModules'][] = function( $out, $mode ) {
		$modules = array(
			'mobile.uploads.schema',
			'mobile.watchlist.schema',
			'mobile.editing.schema',
		);
		// add regardless of mode
		$out->addModules( $modules );
		return true;
	};
}

// Force HTTPS for login/account creation
$wgMFForceSecureLogin = $wmgMFForceSecureLogin;

// Enable X-Analytics logging
$wgMFEnableXAnalyticsLogging = $wmgMFEnableXAnalyticsLogging;

// Blacklist some pages
$wgMFNoMobileCategory = $wmgMFNoMobileCategory;
$wgMFNoMobilePages = $wmgMFNoMobilePages;

// Hack to work around https://bugzilla.wikimedia.org/show_bug.cgi?id=35215
$wgHooks['EnterMobileMode'][] = function() {
	global $wgCentralHost, $wgCentralPagePath, $wgCentralBannerDispatcher, $wgCentralBannerRecorder, $wgCentralAuthCookieDomain;

	$wgCentralHost = str_replace( 'meta.wikimedia.org', 'meta.m.wikimedia.org', $wgCentralHost );
	$wgCentralPagePath = str_replace( 'meta.wikimedia.org', 'meta.m.wikimedia.org', $wgCentralPagePath );
	$wgCentralBannerDispatcher = str_replace( 'meta.wikimedia.org', 'meta.m.wikimedia.org', $wgCentralBannerDispatcher );
	$wgCentralBannerRecorder = str_replace( 'meta.wikimedia.org', 'meta.m.wikimedia.org', $wgCentralBannerRecorder );

	// Hack for bug https://bugzilla.wikimedia.org/show_bug.cgi?id=47647
	if ( $wgCentralAuthCookieDomain == 'commons.wikimedia.org' ) {
		$wgCentralAuthCookieDomain = 'commons.m.wikimedia.org';
	} elseif ( $wgCentralAuthCookieDomain == 'meta.wikimedia.org' ) {
		$wgCentralAuthCookieDomain = 'meta.m.wikimedia.org';
	}

	return true;
};

$wgMFEnableSiteNotice = $wmgMFEnableSiteNotice;
$wgMFEnablePhotoUploadCTA = $wmgMFEnablePhotoUploadCTA;
