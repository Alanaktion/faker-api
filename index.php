<?php
$f3 = require("lib/base.php");

$locale = "en_US";
if(!empty($_GET["locale"]) && in_array($_GET["locale"], array("ar_JO","bg_BG","bn_BD","cs_CZ","da_DK","de_AT","de_DE","el_GR","en_AU","en_CA","en_GB","en_NZ","en_PH","en_US","en_ZA","es_AR","es_ES","es_PE","es_VE","fi_FI","fr_BE","fr_CA","fr_FR","hu_HU","hy_AM","is_IS","it_IT","ja_JP","lv_LV","me_ME","ne_NP","nl_BE","nl_NL","pl_PL","pt_BR","pt_PT","ro_MD","ro_RO","ru_RU","sk_SK","sr_Cyrl_RS","sr_Latn_RS","sr_RS","sv_SE","tr_TR","uk_UA","zh_CN"))) {
	$locale = $_GET["locale"];
}

$faker = Faker\Factory::create($locale);

// Load local configuration if available
if(is_file("config.ini")) {
	$f3->config("config.ini");

	// Disallow unauthorized access if a unique header is required
	if($f3->get("required_header") && false) {
		exit();
	}
}

/**
 * Outputs a JSON object with headers
 * @param  mixed $obj
 * @return boolean
 */
function out($obj) {
	if(!headers_sent()) {
		header("Content-type: application/json");
	}
	$json = json_encode($obj);
	return $json === false ? false : !!print $json;
}

// Handle errors with JSON response
$f3->set("ONERROR", function($f3) {
	$err = array(
		"error" => $f3->get("ERROR.code"),
		"message" => $f3->get("ERROR.text")
	);
	if($f3->get("DEBUG") >= 2) {
		$err["trace"] = $f3->get("ERROR.trace");
	}
	out($err);
});

// Set up endpoints
$f3->route(
	"GET /",
	function($f3, $params) {
		$f3->error(404, "No endpoint specified. See documentation at https://www.mashape.com/alanaktion/faker");
	}
);
$f3->route(
	array(
		"GET /lorem",
		"GET /lorem/@max"
	),
	function($f3, $params) use($faker) {
		$params += array("max" => 400);

		if($params["max"] > 10000) {
			$f3->error(400, "Max parameter cannot exceed 10000");
		}

		out($faker->text($params["max"]));
	}
);
$f3->route(
	array(
		"GET /text",
		"GET /text/@max"
	),
	function($f3, $params) use($faker) {
		$params += array("max" => 400);

		if($params["max"] > 10000) {
			$f3->error(400, "Max parameter cannot exceed 10000");
		}

		out($faker->realText($params["max"]));
	}
);
$f3->route(
	array(
		"GET /int",
		"GET /int/@max",
		"GET /int/@min/@max",
		"GET /rand",
		"GET /rand/@max",
		"GET /rand/@min/@max",
	),
	function($f3, $params) use($faker) {
		$params += array("min" => isset($params["max"]) ? 1 : 0, "max" => getrandmax());
		out(rand($params["min"], $params["max"]));
	}
);
$f3->route(
	array(
		"GET /date",
		"GET /date/@before",
		"GET /date/@before/@format",
	),
	function($f3, $params) use($faker) {
		$params += array("before" => "now", "format" => "Y-m-d H:i:s");
		out($faker->date($params["format"], $params["before"]));
	}
);
$f3->route(
	array(
		"GET /person",
		"GET /person/@gender",
	),
	function($f3, $params) use($faker) {
		$params += array("gender" => null);
		out(array(
			"name" => $faker->name($params["gender"]),
			"phone" => $faker->phoneNumber,
			"email" => $faker->email,
			"address" => $faker->address
		));
	}
);
$f3->route(
	array(
		"GET /people",
		"GET /people/@count",
	),
	function($f3, $params) use($faker) {
		$params += array("count" => 10);

		if($params["count"] > 1000) {
			$f3->error(400, "Count parameter cannot exceed 1000");
		}

		$result = array();
		for ($i = 1; $i < $params["count"]; $i++) {
			$result[] = array(
				"name" => $faker->name,
				"phone" => $faker->phoneNumber,
				"email" => $faker->email,
				"address" => $faker->address
			);
		}
		out($result);
	}
);
$f3->route(
	array(
		"GET /net/@param",
		"GET /net/@param/@browser"
	),
	function($f3, $params) use($faker) {
		$params += array("browser" => null);
		switch($params["param"]) {
			case "domain":
				out($faker->domainName);
				break;
			case "url":
				out($faker->url);
				break;
			case "slug":
				out($faker->slug);
				break;
			case "mac":
				out($faker->macAddress);
				break;
			case "ip":
			case "ipv4":
				out($faker->ipv4);
				break;
			case "ipv6":
				out($faker->ipv6);
				break;
			case "local":
				out($faker->localIpv4);
				break;
			case "ua":
			case "useragent":
				switch($params["browser"]) {
					case "chrome":
						out($faker->chrome);
						break;
					case "firefox":
					case "mozilla":
						out($faker->firefox);
						break;
					case "safari":
						out($faker->safari);
						break;
					case "opera":
						out($faker->opera);
						break;
					case "ie":
					case "internetexplorer":
					case "internetExplorer":
						out($faker->internetExplorer);
						break;
					default:
						out($faker->userAgent);
				}
				break;
			default:
				$f3->error(400);
		}
	}
);
$f3->route(
	array(
		"GET /cc",
		"GET /creditcard",
		"GET /payment"
	),
	function($f3, $params) use($faker) {
		out($faker->creditCardDetails);
	}
);
$f3->route(
	array(
		"GET /color",
		"GET /color/@format",
	),
	function($f3, $params) use($faker) {
		$params += array("format" => "hex");
		switch($params["format"]) {
			case "rgb":
				out($faker->rgbcolor);
				break;
			case "safe":
				out($faker->safeColorName);
				break;
			case "name":
				out($faker->colorName);
				break;
			case "hex":
			default;
				out($faker->hexcolor);
		}
	}
);
$f3->route(
	array(
		"GET /image/@width",
		"GET /image/@width/@height",
		"GET /image/@width/@height/@tag",
	),
	function($f3, $params) use($faker) {
		$params += array("height" => $params["width"], "tag" => null);
		if(!intval($params["height"]) || !intval($params["width"])) {
			$f3->error(400, "Dimensions must be integers.");
		}
		out($faker->imageUrl($params["width"], $params["height"], $params["tag"]));
	}
);
$f3->route(
	"GET /uuid",
	function($f3, $params) use($faker) {
		out($faker->uuid);
	}
);
$f3->route(
	array(
		"GET /hash",
		"GET /hash/@type",
	),
	function($f3, $params) use($faker) {
		$params += array("type" => "md5");
		switch($params["type"]) {
			case "md5":
				out($faker->md5);
				break;
			case "sha1":
				out($faker->sha1);
				break;
			case "sha256":
				out($faker->sha256);
				break;
			default:
				$f3->error(400, "Invalid hash type given, must be md5, sha1, or sha256.");
		}
	}
);
$f3->route(
	"GET /locale",
	function($f3, $params) use($faker) {
		out($faker->locale);
	}
);

$f3->run();
