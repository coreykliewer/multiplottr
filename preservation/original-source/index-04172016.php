<?php
include 'db/db.class.php';
include 'db/plotter.class.php';
$pltr = new Plotter();
include 'checklogin.php';
include 'reglogin.php';



if($_GET['map_id']) {
    list($map_name, $map_info, $map_description, $map_username) = $pltr->getMapBasicInfo($_GET['map_id']);
	$thisMapId = $_GET['map_id']; $thisMapDescription = strip_tags($map_description);
} else {$map_name = 'Plot, save and share.'; $thisMapId='';}

/*<meta property="og:description" content="'.strip_tags($map_description).'" />*/

?>
<!doctype html>
<html>
    <head>
        <title>Multiplottr: Plot, Save and Share.</title>
        
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
        <meta name="apple-mobile-web-app-capable" content="yes" />
        
        <?php if($_GET['map_id']) {echo '<meta property="og:url" content="http://multiplottr.com/?map_id='.$thisMapId.'" />
		<meta property="og:description" content="A custom map created by Multiplottr.com. Multiplottr.com allows you to plot, save and share multiple addresses with an easy to create customized map & markers." />
		';} 
		else {echo '<meta property="og:url" content="http://www.multiplottr.com/" />
		<meta property="og:description" content="Multiplottr.com allows you to plot, save and share multiple addresses with an easy to create customized map & markers."/>
			
		';}?>
        
        
        <meta property="og:title"           content="Multiplottr: <?php echo $map_name; ?>" /> 
        <meta property="og:image"           content="http://www.multiplottr.com/thumbnail.png" />
        <meta name="google-site-verification" content="HryWSTe1imC1tr54xRwCXzFJepCxBlZ4xh2pZh908VI" />
        <meta property="og:type" content="website" />
        
        
        <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
        <link rel="icon" href="favicon.ico" type="image/x-icon">
        <script src="https://maps.googleapis.com/maps/api/js?v=3.13&key=[REDACTED_TOKEN]&sensor=true" type="text/javascript"></script>
        <script src="http://google-maps-utility-library-v3.googlecode.com/svn/tags/markermanager/1.0/src/markermanager.js" type="text/javascript"></script>
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
        <script src="scripts/jquery.ui.map.js"></script>
        <script src="scripts/infobox.js"></script>
        <script src="scripts/placeholder/jquery.placeholder.js" type="text/javascript" charset="utf-8"></script>
        <link href="new.css" rel="stylesheet" type="text/css" />
        <!-- <link rel="stylesheet" href="fontstyles.css"> -->
        <link href="styles.css" rel="stylesheet">
        <link href='http://fonts.googleapis.com/css?family=PT+Sans' rel='stylesheet' type='text/css'>
        <link href='http://fonts.googleapis.com/css?family=Nunito' rel='stylesheet' type='text/css'>
        
        <script src="scripts/jquer-ui/js/jquery-ui-1.10.3.custom.min.js"></script>
        <link rel="stylesheet" type="text/css" href="scripts/jquer-ui/css/start/jquery-ui-1.10.3.custom.css">
        <!-- TinyMCE -->
        <script type="text/javascript" src="../scripts/tiny_mce/tiny_mce.js"></script>
        <script type="text/javascript" src="../scripts/_advertisement.js"></script>
        <script type="text/javascript" src="../scripts/js-url.js"></script>
        <script type="text/javascript" src="../scripts/jquery.autosize-min.js"></script>
        <script type="text/javascript" src="../scripts/markerwithlabel.js"></script>
        
        <script src="http://google-maps-utility-library-v3.googlecode.com/svn/trunk/geolocationmarker/src/geolocationmarker-compiled.js"></script>

        <script type="text/javascript">
   

            tinyMCE.init({
                mode : "exact",
                remove_linebreaks : false,
                elements : "markerTextArea",
                theme : "advanced",
                theme_advanced_buttons1 : "bold,italic,|,link,unlink,image,|,bullist,numlist,|,code",
            });
   
   
           /*tinyMCE.init({
                mode : "exact",
                remove_linebreaks : false,
                elements : "mapDescriptionField",
                theme : "advanced",
                theme_advanced_buttons1 : "bold,italic,|,link,unlink,image,|,bullist,numlist,|,code",
                width: '100%',
                height: '275px',
				convert_newlines_to_brs : true,
                setup : function(ed) {
                    ed.onInit.add(function(ed) {
                        ed.pasteAsPlainText = true;
                    });
                }
            }); */ 
   
   
        </script>
        <!-- /TinyMCE -->
      <script type="text/javascript" src="../scripts/main.js"></script>

        <!--[if gte IE 9]>
       <style type="text/css">
         .gradient {
            filter: none;
         }
       </style>
     <![endif]-->
     
     

    
    </head>
    <body>
<?php
include 'localsession.php';
?>
              

        <div id="fb-root"></div>
        <script>(function(d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) return;
            js = d.createElement(s); js.id = id;
            js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=13993495363";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));
        </script>

<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-75352-2', 'multiplottr.com');
  ga('require', 'displayfeatures');
  ga('send', 'pageview');

</script>


        <div class="infoBoxWrap centered" id="loadLastPlotted">Start a New Map?
            <div class="buttonHolder"> <a class ="button" id="clearYes" href="javascript:void(0)">Yes</a><a class ="button" id="clearNo" href="javascript:void(0)">No</a></div>
        </div>
        <div class="infoBoxWrap centered" id="alertBox">
        	<span></span>
   			<div class="buttonHolder">
       		<a class ="button" id="alertYes" href="javascript:void(0)">Yes</a>
       		<a class ="button" id="alertNo" href="javascript:void(0)">No</a>
           <a class ="button" id="alertOk" href="javascript:void(0)">OK</a> 
    	</div>
        </div>
        <div class="infoBoxWrap centered" id="progressContainer">
            <p>Progress: <span id="numOfAddresses"></span></p>
            <div id="progressbar"></div>
        </div>
        <div class="infoBoxWrapAlert centered" id="modalAlert">
            <article></article>
            <div class="buttonHolder"> <a class ="button" id="buttonOK" href="javascript:void(0)">OK</a></div>
        </div>

        <div id="modalBack"></div>
        <div id="topBannerNav"> <!---->
            <div id="logo"><span>Multiplottr</span></div><h1>Plot, save and share multiple addresses with an easy to create customized map.</h1>
            <ul id="loginContainer">
            <a href="javascript:void(0)" id="loginRegister"  href="javascript:void(0)">
                <div data-icon="y" class="expand"></div>
                <div data-icon="w" class="contract"></div>
                <span class="caps">Login | Register</span></a> 
            <a href="javascript:void(0)" id="loggedIn"       href="javascript:void(0)">
                <div data-icon="y" class="expand"></div>
                <div data-icon="w" class="contract"></div>
                <span class="caps"></span></a>
            </ul> 
            <div id="mobilNav"><div id="mobilLogin" class="icon-user-outline"></div><div id="mobilMenu" class="icon-reorder"></div></div>
        </div>
        <div id="popOutWrapper" >
            <div id="batchAddress">
                <form id="batchAddresses" method="post">
                    <section>
                        <textarea id="batchAddressesField" name="batchAddressesField"  placeholder="Enter each address 1 line at a time. Unregistered users can plot 25 addresses at a time."></textarea>
                    </section>
                </form>
                <form  id="mapTool" method="post" >
                    <section>
                        <textarea id="markerLocationField" name="markerLocationField" ></textarea>
                    </section>
                    <section class="hide">
                        <input type="text" id="mapTitleFieldSubmit" name="mapTitleFieldSubmit">
                        <textarea id="mapDescriptionFieldSubmit" name="mapDescriptionFieldSubmit" ></textarea>
                        <input type="text" id="mapUrlParam" name="mapUrlParam" />
                        <input id="saveMapAction" type="hidden" name="action" value="">
                        <input id="saveOverwriteMapOption" type="hidden" name="saveOverwriteMapOption" value="">
                    </section>
                </form>
                <form action="download_kml.php" method="post" name="kmlform" id="kmlform" class="hide">
      				<textarea name="kmlBox" id="kmlBox" cols="" rows="10" class="hide" ></textarea>
      				<input type="hidden" name="kmlname" id="kmlname" value="<?php echo $map_name ?>" />
      				<input type="hidden" name="kmldescription" id="kmldescription" value="<?php echo $map_description  ?>" />
    			  </form>
                <div class="content">
                 <div data-icon="p" class="close"></div>   <h2>Plot Multiple Addresses</h2>
                    <p>Plot, save and share multiple addresses with an easy to create customized map.</p><p>Place each address on its own line and hit the "Submit" button below..</p>
                    <ul id="defaultSettings" class="buttonHolder" >
                            

                        <li><span class="currentMarker"></span>
                            <label>Default Marker:</label>
                        </li>
                        <li>
                            <label>Default Group: </label>
                            <span id="currentGroupNameDisplay"></span></li>
                        <li>
                            <a id="batchGeocode" class="button4 buttonB" href="javascript:void(0)" >
                                Submit<div class="icon" data-icon="3"></div></a>
                        </li>
                        <!-- <a id="plotTheseAddresses" class="button2" href="javascript:void(0)">Plot Addresses</a>-->
                    </ul>
                </div>
                
            </div>
            <div id="mapSettings">
                <div id="urlAttributes" >
                    <div class="content"><div data-icon="p" class="close"></div>
                    <h2 class="mainHeader">Map Settings</h2>
                      <div class="buttonContainer">
						<a id="zoomView"      class="button buttonC" href="javascript:void(0)" >Lock Zoom Level <br>& Map Center</a>
						<a id="loadPreviousMap"   class="mapsetbutton button buttonC" href="javascript:void(0)" >Load Last Plotted</a>
                      <a id="downloadKML"   class="mapsetbutton button buttonC" href="javascript:void(0)" >Download KML File</a>  
                      </div>
                    	<a id="advancedSettingsButton" href="javascript:void(0)" class="button buttonC"><div data-icon="y" class="expand"></div>
                			<div data-icon="w" class="contract"></div>Advanced</a>
                        
                        
                      
                        
                        
                   		<ul id="saveMapSettings">
                            <li><p>You must provide a map title for your map to save.</p></li>
                            <?php if(isset($_GET['map_id']) && $_GET['map_id'] ) { ?>
                            <li><input type="radio"  name="saveMapOptions" class="radioWrap" value="overwritemap" checked><label>Overwrite Map</label></li>
                            <li><input type="radio"  name="saveMapOptions" class="radioWrap" value="newmap"><label>Save as New Map</label></li>
                            <?php } ?>
                 			</ul>
                    		<a id="saveMapButton" href="javascript:void(0)" class="button">Save Map</a>

                        <ul id="advancedMapSettings">
							<form id="urlMapSettings" method="post">
                                <li class="bulletLine">Hide Zoom Control
                                    <input type="checkbox" id="mapControl"  value="true" name="mapControl" />
                                </li>
                                <li class="bulletLine">Hide Map Type
                                    <input type="checkbox" id="hideMapType"  value="true"  />
                                </li>
                                <li class="bulletLine">Show Map Overview
                                    <input type="checkbox" id="showMapOverview"  value="true"  />
                                </li>
                                <li class="bulletLine">Zoom Control Type
                                    <select  class="smallerFormField" id="mapControlSize"  >
                                        <option>small</option>
                                        <option>large</option>
                                    </select>
                                </li>
                                <li class="bulletLine">Min Zoom Level
                                    <select  class="smallerFormField" id="minZoom"  >
                                        <option>1</option>
                                        <option>2</option>
                                        <option>3</option>
                                        <option>4</option>
                                        <option>5</option>
                                        <option>6</option>
                                        <option>7</option>
                                        <option>8</option>
                                        <option>9</option>
                                        <option>10</option>
                                        <option>11</option>
                                        <option>12</option>
                                        <option>13</option>
                                        <option>14</option>
                                        <option>15</option>
                                        <option>16</option>
                                        <option>17</option>
                                        <option>18</option>
                                        <option>19</option>
                                        <option>20</option>
                                    </select>
                                </li>
                                <li class="bulletLine">Max Zoom level
                                    <select  class="smallerFormField" id="maxZoom"  >
                                        <option>1</option>
                                        <option>2</option>
                                        <option>3</option>
                                        <option>4</option>
                                        <option>5</option>
                                        <option>6</option>
                                        <option>7</option>
                                        <option>8</option>
                                        <option>9</option>
                                        <option>10</option>
                                        <option>11</option>
                                        <option>12</option>
                                        <option>13</option>
                                        <option>14</option>
                                        <option>15</option>
                                        <option>16</option>
                                        <option>17</option>
                                        <option>18</option>
                                        <option>19</option>
                                        <option>20</option>
                                    </select>
                                </li>
                                
                                
                                
                                 <!--<li class="bulletLine">Show Legend
                                    <input type="checkbox" id="showMapLegend"  value="true" name="showMapLegend" />
                                </li>
                                <li class="bulletLine">Show Groups
                                    <input type="checkbox" id="showMapGroup"  value="true" name="showMapGroup" />
                                </li>
                               <li class="bulletLine">Map Width
                                    <input type="text" id="mapWidth" name="mapWidth" />
                                </li>
                                <li class="bulletLine">Map Height
                                    <input type="text" id="mapHeight" name="mapHeight" />
                                </li> -->
                                <li class="bulletLine hide">Map ID
                                    <input type="text" id="map_id" name="map_id"/></li>
                                <li class="bulletLine hide">MapDatabase URL
                                    <input type="text" id="mapDatabaseURL"  />
                                </li>
                                <!--<li class="bulletLine hide">Show Custom Map
                                    <input type="checkbox" id="mapCustom"  value="true" />
                                </li> -->
                                <li class="bulletLine">customMap URL
                                    <input type="text" id="customMapURL" />
                                </li>
                                <li class="bulletLine topSpacer">
									<a id="embedMap" href="javascript:void(0)" class="button3 buttonB">Embed Map</a>                                </li>
                                <li class="bulletLine">
									<a id="clearParameters" href="javascript:void(0)" class="button3 buttonB">Clear Settings</a>                                </li>
                                <li class="bulletLine hide">
                                <input type="text" id="urlParam"  />
                                </li>
                                <li class="bulletLine">
                                <a id="updateURL" href="javascript:void(0)" class="button3 buttonB">Submit Changes<div class="icon" data-icon="3"></div></a>
                                </li>
                                <li class=" hide bulletLine">
                                    <input type="hidden" name="saveMap" value="1">
                                    <input name="" type="submit">
                                </li>
                            </form>
                        </ul>
                    </div>
                    <section>
                        <h2>Map Title</h2>
                        <input type="text" id="mapTitleField" placeholder="New Map">
                        <h2>Map Description</h2>
                      <textarea id="mapDescriptionField" ></textarea>
                    </section>
                    
                </div>
            </div>
            <div id="mapTitle">
                <div id="expandClose">
                    <div data-icon="y" class="expand"></div>
                    <div data-icon="w" class="contract"></div>
                </div>
                <header></header>
                <div id="mapDescript"></div>
            </div>

            <!--   <ul id="tabNav">
           <li id="batchAddressTab" class="collapsed">Plot Addresses</li>
           <li id="addMarkerTab" class="collapsed">Place Marker</li> 
         </ul>--> 
        </div>
        <div id="loginRegisterBox">
				<ul>
                    <form id="loginForm" name="loginForm" method="post" autocomplete="off"> 

                        <h2>Login</h2>
                        <li>
                            <input id="username" name="username" type="email" placeholder="Email">
                        </li>
                        <li>
                            <input id="password" name="password" type="password" placeholder="Password">
                        </li>
                        <li >
                        	<input id="loginSubmit" name="login" class="button5 buttonA" type="submit" value="Login">
                         
                        </li>
                        <li><input type="checkbox" id="saveLogin"  class="checkWrap" value="remember" ><label>Remember Me</label>
                           <!-- <a id="userLogin" class="button3" href="javascript:void(0)" >Login</a> --><a class="smallType" id="forgotCredentialsLink" href="javascript:void(0)" >Forgot Password</a> </li>
                    </form>   
                    <h2 id="registerLink"><a href="javascript:void(0)">Register</a></h2>
                    <div id="registerBox">
                        <form id="registerForm" name="registerForm" method="post">
                            <li><input id="regUsername" name="regUsername" type="text" placeholder="Username"></li>
                            <li id="checkEmail"><div class="check"></div><input id="email" type="email" name="email" placeholder="Email"></li>
                            <li id="checkPW"><div class="check"></div><input id="regPassword" name="regPassword" type="password" placeholder="Password"></li>
                            <li id="confCheckPW"><div class="check"></div><input id="confirmPassword" type="password" placeholder="Confirm Password"></li>
                            <li class="bottomSpacer"><input id="register" name="register" type="submit" class="button5 buttonA" value="Register"><!--a id="register" class="button3" href="javascript:void(0)" >Register</a--></li>
                        </form>  
                    </div>
                    <section id="forgotCredentials">
                        <h2 class="bottomSpacerHalf">Forgot Password</h2>
                        <!--<li >
                            <label>Forgot Username</label>
                            <input id="forgotUsername" type="checkbox" value="">
                            <label>Forgot Password</label>
                            <input id="forgotPassword" type="checkbox" value="">
                        </li> -->
                        <form id="forgotCredentialsForm" name="forgotCredentialForm" method="post">
                        <li class="topSpacerHalf">
                            <input id="email" name="email" type="text" placeholder="Email">
                        </li>
                        <li class="bottomSpacer"><input id="forgotCredentialSubmit" name="forgotCredentialSubmit" type="submit" class="button5 buttonA" value="Submit"><a class="smallType" id="hideCredentials" href="javascript:void(0)" >Hide</a></li>
                        </form>
                    </section>
                </ul>
            </div>
            <div id="loggedInBox">
                <ul>
                    <h2>My Account</h2>
                    <li><a id="logOut" class="button3" href="/logout.php" >Log Out</a><!--a id="logOut" class="button3" href="javascript:void(0)" >Log Out</a--></li>
                    <li><a id="userSettings" class="button3" href="javascript:void(0)" >User Settings</a></li>
                </ul>
            </div>
		<div id="mapLegend">
			
            

            
                <!--<li class="miniNav"><a id="helpInfo" href="javascript:void(0)"></a></li>
                 <li class="miniNav"><a id="facebookIcon" target="_blank" href="http://www.facebook.com/groups/multiplottr/"></a></li>
                <li class="miniNav"><a id="twitterIcon" href="https://twitter.com/multiplottr" target="_blank"></a></li>
                <li class="miniNav"><a id="googlPlusIcon" href="https://plus.google.com/communities/113028818505618822695" target="_blank"></a></li>
                <li class="miniNav"><a id="tumblrIcon" href="http://multiplottr.tumblr.com/" target="_blank"></a></li> -->
           
            <div id="mainButtonContainer">
<ul id="miniNavContainer">
                <li class="miniNav"><a id="aboutMP" href="javascript:void(0)"><div data-icon="o"></div>ABOUT</a></li>
                <li class="miniNav"><a id="blogLink"href="http://multiplottr.tumblr.com/" target="_blank"><div data-icon="j"></div>BLOG</a></li>
                <li class="miniNav"><a id="contactUsButton" href="javascript:void(0)"><div data-icon="H"></div>CONTACT US</a></li> </ul>
                <section id="editOptions">
                    <header id="mapSettingsNav">
                        <div data-icon="l" class="icon"></div>
                        <span>Map<br>Settings</span>
                    </header>
                    <header id="myMaps">
                        <div data-icon="B" class="icon"></div>
                        <span>My<br>Maps</span>
                    </header>
                </section>
                <section>
                    <header  id="batchAddressTab">
                        <div data-icon="W" class="icon"></div>
                        <span>Plot<br>Addresses</span>
                    </header>
                    <header  id="placeMarkerHeader">
                        <div data-icon="r" class="icon"></div>
                        <span>Place<br>Marker</span></header>
                </section>
                <section id="cleaRNSave">
                    <header  id="cleanMap">
                        <div data-icon="X" class="icon"></div>
                        <span>New Map</span>
                    </header>
                    <header id="saveMap" class="readyToSave">
                        <div data-icon="J" class="icon"></div>
                        <span>Save Map</span>
                        <div id="unfinishedMarkers"><input id="markerCount" class="hide" type="text" value="0"></div>
                    </header>

                </section>
            </div><div id="rightContainer">
            <div class="expand" id="legendHeader">
                <div data-icon="y" class="expand"></div>
                <div data-icon="w" class="contract"></div>
                <span>Legend</span>
            </div>

			            
			
            <div id="legendItems">
                <div>
                <h2>Groups</h2>
                <div id="groupControlDiv"></div>
                </div>
                 <h2><span><?php echo file_get_contents("images/alphabetical1.svg"); ?></span>Legend </h2>
                <div id="legendDiv" > </div>
            </div>
            <!-- look for "// DESCRIPTION: looks for extra values in URL" in main.js to control #sharemap  -->
            <div id="shareMap" href="javascript:void(0)">
            <script language="javascript">
    			function fbshareCurrentPage()
    			{window.open("https://www.facebook.com/sharer/sharer.php?u="+escape(window.location.href), '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=300,width=600');
				return false; }
				</script>
            	<h2>Share</h2>
                <ul>
					<li class="shareLinks"><a data-icon="k" href="javascript:fbshareCurrentPage()" target="_blank" alt="Share on Facebook"></a></li>
					<li class="shareLinks">
                    <a href="https://twitter.com/share" target = "_blank" data-icon="#" data-dnt="true" data-count="none" data-via="twitterapi"></a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script></li>
					<!--<li class="shareLinks"><a data-icon="&"></a></li> -->
					<li class="shareLinks"><a target="_blank" data-icon="$" href="mailto:?subject=Multiplottr Map: <?php echo $map_name; ?>&amp;body=Here is the map that I made <?php echo "http://www.multiplottr.com/?map_id=$thisMapId"; ?>." title="Share by Email"></a>

                  </li>
                </ul>
            </div>
            <a class="rightNavButton" onclick="gmapPrint();" >Print Map</a>
            <a id="showLocation" href="javascript:void(0)" >Show My Location</a>
            <div id="shareLocationBox">
           		<div id="linkBox"></div>
            	<div id="tweetBox"><span></span></div>
            </div>
            

            <!--<div id="adSideBar"> 
                <script type="text/javascript">
                google_ad_client = "ca-pub-7787442361581894";
                google_ad_slot = "2158987558";
                google_ad_width = 234;
                google_ad_height = 60;
                
                </script> 
                <script type="text/javascript"
                        src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
                </script></div>//-->
           <!-- <div id="rssFeed">
            <script type="text/javascript" src="http://output60.rssinclude.com/output?type=js&amp;id=779535&amp;hash=f2f38a56de586cdffb3ef3f0c42513fe"></script>
            </div>     -->
            <div id="socialBar">

                <div class="fb-like" data-href="http://www.multiplottr.com/" data-send="false" data-layout="button_count" data-width="225" data-show-faces="false"></div>
                <!-- Place this tag where you want the +1 button to render. -->
                <div class="g-plusone" data-size="medium"></div>

                <!-- Place this tag after the last +1 button tag. -->
                <script type="text/javascript">
                (function() {
                    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
                    po.src = 'https://apis.google.com/js/plusone.js';
                    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
                })();
                </script>
            </div>
           <div id="squareAd"> 
          <script type="text/javascript"><!--
google_ad_client = "ca-pub-7787442361581894";
/* OnlineCustomMaps - Square - Right */
google_ad_slot = "8034147954";
google_ad_width = 250;
google_ad_height = 250;
//-->
</script>
<script type="text/javascript"
src="//pagead2.googlesyndication.com/pagead/show_ads.js">
</script> 
   </div>       
        </div>
        
        </div>
        <div id="mapContainer"> <a name="maptop" id="maptop"></a>
        	<div id="quickSearch">
           	<ul>
            		
        			<li><textarea class="force35pxheight" placeholder="Plot Multiple Addresses. One address per line."></textarea></li>
              		<li class="liFix"><span class="currentMarker" ></span></li>
              		<li class="quickSearchLiFix"><a href="javascript:void(0)">Plot</a></li>
                    
           	</ul>
			</div>
        <div id="markerSelection" >
        	<h1>
				<div data-icon="p" class="close"></div>
   				Place Marker
           </h1>
        	
           <div class="tip"><span><div data-icon="w" class="contract"></div></span>Click on the map to place a marker.</div>
           <section>
			<h2>Default Marker</h2>
            <a href="javascript:void(0)" class="changeMarker">Show Available Markers</a>
           <div class="currentMarker"></div>
                
                <ul id="markerList">
                    <li><img src="../images/REDMarker.png" width="20" height="34" alt="RED"></li>
                    <li><img src="../images/ORANGEMarker.png" width="20" height="34" alt="ORANGE"></li>
                    <li><img src="../images/YELLOWMarker.png" width="20" height="34" alt="YELLOW"></li>
                    <li><img src="../images/GREENMarker.png" width="20" height="34" alt="GREEN"></li>
                    <li><img src="../images/LIMEMarker.png" width="20" height="34" alt="LIME"></li>
                    <li><img src="../images/BLUEMarker.png" width="20" height="34" alt="BLUE"></li>
                    <li><img src="../images/CYANMarker.png" width="20" height="34" alt="CYAN"></li>
                    <li><img src="../images/VIOLETMarker.png" width="20" height="34" alt="VIOLET"></li>
                    <li><img src="../images/PURPLEMarker.png" width="20" height="34" alt="PURPLE"></li>
                    <li><img src="../images/PINKMarker.png" width="20" height="34" alt="PINK"></li>
                    <li><img src="../images/WHITEMarker.png" width="20" height="34" alt="WHITE"></li>
                    <li><img src="../images/GREYMarker.png" width="20" height="34" alt="GREY"></li>
                    <li><img src="../images/BLACKMarker.png" width="20" height="34" alt="BLACK"></li>
                    <li><img src="../images/NUMBERMarker.png" width="20" height="34" alt="NUMBER"></li>
                    <li><img src="../images/SMALLREDMarker.png" width="20" height="34" alt="SMALLRED"></li>
                    <li><img src="../images/SMALLORANGEMarker.png" width="20" height="34" alt="SMALLORANGE"></li>
                    <li><img src="../images/SMALLYELLOWMarker.png" width="20" height="34" alt="SMALLYELLOW"></li>
                    <li><img src="../images/SMALLGREENMarker.png" width="20" height="34" alt="SMALLGREEN"></li>
                    <li><img src="../images/SMALLBLUEMarker.png" width="20" height="34" alt="SMALLBLUE"></li>
                    <li><img src="../images/SMALLGREYMarker.png" width="20" height="34" alt="SMALLGREY"></li>
                    
                </ul>
                
                <ul id="customizedMarker">
                <li>
                                <input id="markerURL" type="text" placeholder="Custom Marker URL">
                            </li>
                            <li><a id="addNewMarkerURL" class="button" href="javascript:void(0)" >Add Custom Marker URL</a></li>
                </ul>
                <div class="container">
                    <input id="markerName" type="text" class="hide" >
                    <textarea id="tinyMCEHolder" class="hide"></textarea>
                    <ul>
                        <span >
                            
                        </span>
                        <li class="dividerLine">
                      <!--  </div>
                <div data-icon="w" class="contract"></div>
                <span>Legend</span> -->
                            <h2 id="groupHeader">Groups<span><div data-icon="y" class="expand"></div></span></h2>
                        </li>
                        <div id="mrkrGroups">
                        <li>
                            <select id='allGroupNames'>
                            </select>
                        </li>
                        <li>
                            <input id="currentGroupName" type="text">
                        </li>
                        </div>
                        <section class="markerInfoWindow">
                            <li class="dividerLine">
                                <h2>Marker Header</h2>
                            </li>
                            <li>
                                <input id="markerHeader" name="Marker Header" type="text" placeholder="Enter Marker Header">
                            </li>
                            <li>
                                <h2>Marker Description</h2>
                            </li>
                            <li>
                                <textarea id="markerTextArea" cols="" rows=""></textarea>
                            </li>
                        </section>
                        <li><a id="plotLocation" href="javascript:void(0)" >Plot My Location</a></li>
                    </ul>
                </div>
                <ul id="addAddress">
                    <li class="hide">
                        <input id="markerAddress" type="text" placeholder="Marker Address">
                    </li>
                    <li class="hide"><a id="geoCodeAddress" class="button3" href="javascript:void(0)" >Plot Address</a></li>
                    <li class="hide"><a id="placeMarker"    class="button3 hide" href="javascript:void(0)" >
                            <input id="plotMarker" type="checkbox" value="">
                            Place Marker </a></li>
                    
                    <li class="dividerLine"></li>
                    <li><a id="updateMarker" class="button4 buttonA" href="javascript:void(0)" >Update Marker</a></li>
                    <li><a id="submitMarker" class="button4 buttonA" href="javascript:void(0)" >Submit Marker</a></li>
                </ul>
            </section>
        </div>
            <div id="introText">
           		<?php
				require($DOCUMENT_ROOT . "includes/about.php");
				?>
            </div>
            <div id="shareBox"><ul><li id="emailShare"></li></ul></div>
			 <div id="myMapsBox">
           	<h1>
              		<div data-icon="p" class="close"></div>
                  My Maps</h1>
              <div class="tableContainer">     
         		<table>
              		<tr class="headerList">
              			<th class="title">Title</th>
                      <th class="date">Last Save</th>
                      <th class="delete"></th>
    				</tr>
                  <?php
						if (isUserLoggedIn()) {
							//get user's maps listings
							echo $pltr->getMapsListingsModule($_user['user_id']);
            			} else { ?>
                        <tr>
                   		<td class="title">n/a</td>
							<td class="date">n/a</td>
							<td class="delete">n/a</td>
                        </tr>    
                  <?php } ?>
				</table>    
              </div>  
            </div>
            <div id="userAccountBox">
                <h1>
                    <div class="closeBox"></div>
                    My Account</h1>
                <ul class="content">
                    <li id="userName"><label>Username</label><span>Guest<span></li>
                    <li><label>Password</label><a href="javascript:void(0)"id="changePassword" >Change</a></li>
                    <form id="changePasswordForm" method="post">
                        <li><input id="chngPw" name="regPassword" type="password" placeholder="Password"></li>
                        <li><input id="confirmChngPw" type="password" placeholder="Confirm Password"></li>
                        <li><input type="submit" name="submitPw" value="Submit"></li>
                    </form>
                    <li><label>Email</label><a href="javascript:void(0)" id="changeEmail">[REDACTED_EMAIL]</a></li>

                    <form id="changeEmailForm" method="post">
                        <li><input id="chngEml" name="chngEml" type="text" placeholder="New Email"></li>
                        <li><input id="confirmChngEml" type="text" placeholder="Confirm Email"></li>
                        <li><input type="submit" name="submitEml" value="Submit"></li>
                    </form>

                    <form id="delelteAccountForm" method="post">
                        <li><input name="action" type="hidden" value="deleteaccount"></li>
                        <li><input type="submit" name="submitDelAcc" value="Delete Account"></li>
                    </form>
                </ul>
            </div>
            <div id="contactUsBox">
                <h1>
                    <div class="closeBox"></div>
                    Contact us</h1>
                <div class="content"><a href="mailto:[REDACTED_EMAIL]">Email Us</a> <!--
                    <div id="wufoo-z7x4m1">
                        Fill out my <a href="http://coreykliewer.wufoo.com/forms/z7x4m1">online form</a>.
                    </div>
                    <script type="text/javascript">var z7x4m1;(function(d, t) {
                    var s = d.createElement(t), options = {
                        'userName':"[REDACTED_CONFIG_VALUE]", 
                        'formHash':'z7x4m1', 
                        'autoResize':true,
                        'height':'437',
                        'async':true,
                        'header':'hide'};
                    s.src = ('https:' == d.location.protocol ? 'https://' : 'http://') + 'wufoo.com/scripts/embed/form.js';
                    s.onload = s.onreadystatechange = function() {
                        var rs = this.readyState; if (rs) if (rs != 'complete') if (rs != 'loaded') return;
                        try { z7x4m1 = new WufooForm();z7x4m1.initialize(options);z7x4m1.display(); } catch (e) {}};
                    var scr = d.getElementsByTagName(t)[0], par = scr.parentNode; par.insertBefore(s, scr);
                })(document, 'script');</script>-->
                </div>
            </div>

            <div id="overMapAdContainer"> <a  class="closeAd">close</a>
                <div class="blockedAd">Advertisement Blocked</div>
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- OnlineCustomMaps Responsive -->
<ins class="adsbygoogle onlinecustommaps-responsive"
     style="display:inline-block"
     data-ad-client="ca-pub-7787442361581894"
     data-ad-slot="2843519154"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
            </div>
<?

require_once 'mpcMain.php';

$map = new MultiplottrMap();

$width = $_GET['mapWidth'];
$height = $_GET['mapHeight'];
$zoomy = $_GET['mapZoom'];
$metroFile = $_GET['mapDatabase'];
$myMaxZoom = $_GET['maxZoom'];
$myMinZoom = $_GET['minZoom'];
$myControl = $_GET['mapControl'];
$myCustomMap = $_GET['mapCustom'];
$myCustomMapURL = $_GET['mapCustomURL'];
$myCustomFormat = $_GET['mapCustomFormat'];
$myType = $_GET['mapType'];
$myOverview = $_GET['mapOverview'];
$myControlSize = $_GET['mapControlSize'];
$myLegend = $_GET['mapLegend'];
$myDatabase = $_GET['mapDatabase'];
$myGroup = $_GET['mapGroup'];
$myLocation = $_GET['location'];
$showLabels = $_GET['showLabels'];
$infoWindowSize = '300';




$bunchOaddresses = $_POST['markerLocationField'];
$myDefaultMarker = $_POST['MarkerColor1'];
$mapTitle = $_POST['mapTitleFieldSubmit'];
$mapDescription = $_POST['mapDescriptionFieldSubmit'];

$myDefaultGroupName = $_POST['groupname1'];

$term = $_SERVER['HTTP_REFERER'];

if (($width == '0') || ($width == '')) {
    $width = '100%';
}
if (($height == '0') || ($height == '')) {
    $height = '100%';
}
if ($zoomy == '') {
    $zoomy = '11';
}
if ($myMaxZoom < 0 || $myMaxZoom > 19 || $myMaxZoom == '') {
    $myMaxZoom = 20;
}
if ($myMinZoom < 0 || $myMinZoom > 19 || $myMinZoom == '') {
    $myMinZoom = 2;
}
if ($myCustomFormat != 'jpg') {
    $myCustomFormat = 'png';
}




$map->setWidth($width);
$map->setHeight($height);
$map->setZoom($zoomy);
$map->setMinZoom($myMinZoom);
$map->setMaxZoom($myMaxZoom);
$map->setControlSize($myControlSize);
$map->setMapCustomURL($myCustomMapURL);
$map->setMapCustomFormat($myCustomFormat);
$map->setMapTitle($mapTitle);



if ($myControl == 'true') {
    $map->setControl(false);
} else {
    $map->setControl(true);
}

if ($showLabels == 'true') {
    $map->setLabels(true);
} else {
    $map->setLabels(false);
}

if ($myCustomMap == 'true') {
    $map->setCustomMap(true);
} else {
    $map->setCustomMap(false);
}
if ($myType == 'true') {
    $map->setType(false);
} else {
    $map->setType(true);
}
if ($myOverview == 'true') {
    $map->setOverview(true);
} else {
    $map->setOverview(false);
}
if ($myLegend == 'false') {
    $map->setLegend(false);
} else {
    $map->setLegend(true);
}
if ($myCustomMapURL <> '') {
	 $map->setCustomMap(true);
    $map->setType(false);
}


$map->showEdit(true);

function nl2brStrict($text, $replacement = '`') {
    return preg_replace("((\r\n)+)", trim($replacement), $text);
}

function curPageURL() {
    $pageURL = 'http';
    if ($_SERVER["HTTPS"] == "on") {
        $pageURL .= "s";
    }
    $pageURL .= "://";
    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
    } else {
        $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
    }

    $ch = curl_init($pageURL);
    curl_exec($ch);
}

function writeToFlatFile($i) {
    $newcontents = $_POST["markerLocationField"];
    if (isset($_POST['markerLocationField'])) {
        $fp = fopen($i, "w");
        if (!$fp) {
            echo 'Error: Cannot open file.';
            exit;
        }
        fwrite($fp, $newcontents);
        fclose($fp);
    }
}


// from map_id

if (($map_info <> null)&&($bunchOaddresses == null)) {
	
	
    $myStr = nl2brStrict($map_info);
    $myStr = str_replace("\t", " ", $myStr);
	$myStr = addslashes($myStr); //single quote fix
	//single quote 
	/*
	$myStr = str_replace("\'", "'", $myStr);
	$myStr = str_replace('\"', '"', $myStr);
	$myStr = str_replace('\\"', '"', $myStr);
	$myStr = str_replace('\\\"', '"', $myStr);
	*/
	//end single quote
	
	if (strpos($myStr,'"') !== false) { 
		if (strpos($myStr,'\"') === false) {
			$myStr = str_replace('"', '\"', $myStr);
		}
	}
	
	if (strpos($myStr,'"') !== false) { 
		if (strpos($myStr,'\"') === false) {
			$myStr = str_replace('"', '\"', $myStr);
		}
	}
	if (strpos($myStr,"'") !== false) { 
		if (strpos($myStr,"\'") === false) {
			$myStr = str_replace("'", "\'", $myStr);
		}
	}


	$myStr = str_replace("Multiplottr formatted data.", "", $myStr);
    $lines = array();
    $lines = explode("`", $myStr);
	$lines = array_unique($lines);
    $lineCount = count($lines);
    for ($i = 0; $i < $lineCount; $i++) {
		//$field2 = addslashes($field2);
		//$field2 = str_replace("'", "\\'", $field2);
        list ($field1, $field2, $field3, $field4) = split('\|', $lines[$i]);
		$field1 = str_replace(array("***EDITTED_CONTENT_LOCATION***"), "", $field1);
        if ($field1<>'') {$map->addAddress($field1, $field2, $field3, $field4);}
        $line = $lines[$i];
        $line = htmlspecialchars($line, ENT_QUOTES);
        $line = preg_replace('/\p{C}+/u', '\\n', $line);
		 $line = str_replace(array("***EDITTED_CONTENT_LOCATION***"), "", $line);
		
		
        echo "
			<script type='text/javascript'>
      		$(document).ready(function(){
				var line = '" . $line . "'; 
				if (line !== '\\n') {
        			$('#markerLocationField').append('" . $line . "\\n');	
					dataArray.push('" . $line . "')
					dataArrayCount++
					
				 		}

			$('#quickSearch').hide();
			$('#mapLegend #mainButtonContainer #cleaRNSave').show();
			
			});
			
   			</script>
			";
    }
	
	
	
	if (($map_username !== $_user['user_username'])) {
    	$map->showEdit(false);
	}  
	
	
}

// $bunchOaddresses is from $_POST['markerLocationField'];

if ($bunchOaddresses <> null) {
    if (substr($myDatabase, 0, 2) == "da") {
        writeToFlatFile($myDatabase);
    } else {
       $myStr = nl2brStrict($bunchOaddresses);
        $myStr = str_replace("\t", " ", $myStr);
		$myStr = addslashes($myStr); //single quote fix
        $lines = array();
        $lines = explode("`", $myStr);
		 $lines = array_unique($lines);
        $lineCount = count($lines);
        for ($i = 0; $i < $lineCount; $i++) {
			$field2 = addslashes($field2);
			$field2 = str_replace("'", "\\'", $field2);
            list ($field1, $field2, $field3, $field4) = split('\|', $lines[$i]);
			if ($field1<>'') {$map->addAddress($field1, $field2, $field3, $field4);}
            
			
            $line = $lines[$i];
            $line = htmlspecialchars($line, ENT_QUOTES);
			$line = str_replace("'", "", $line);
            $line = preg_replace('/\p{C}+/u', '\\n', $line);
            echo "
			<script type='text/javascript'>
      		$(document).ready(function(){
				var line = '" . $line . "'; 
				if (line !== '\\n') {
        			$('#markerLocationField').append('" . $line . "\\n');	
					dataArray.push('" . $line . "')
					dataArrayCount++
		 		}
				$('#mapLegend #mainButtonContainer #cleaRNSave').show();
			});
   			</script>
			";
        }
    }
}

// $myDatabase is from flat file database

if ($myDatabase <> "") {
    $fp = fopen($myDatabase, 'r');
    if (!$fp) {
        echo 'ERROR: Unable to open file.';
        exit;
    }
    while (!feof($fp)) {
        $line = fgets($fp, 1024); //use 2048 if very long lines
        list ($field1, $field2, $field3, $field4) = split('\|', $line);
		// single quote
        //	$addressField = nl2brStrict2($field2);
        //$field2 = htmlspecialchars($field2, ENT_QUOTES);
		// single quote end
        if ($field1<>'') {$map->addAddress($field1, $field2, $field3, $field4);}
        $line = htmlspecialchars($line, ENT_QUOTES);
		$line = str_replace("'", "", $line);
        $line = preg_replace('/\p{C}+/u', '\\n', $line);

        echo "
		<script type='text/javascript'>
		loadLocalStorage=false;
      	$(document).ready(function(){
			var line = '" . $line . "'; 
			if (line !== '\\n') {
        		$('#markerLocationField').append('" . $line . "');	
				dataArray.push('" . $line . "')
				dataArrayCount++
		 	}
			$('#mapLegend #mainButtonContainer #cleaRNSave').show();
		});
   		</script>
		";
    }
    fclose($fp);
}

if (!empty($myLocation)) { $map->showEdit(false); $map->addAddress('~'.$myLocation, 'Here I am.', 'BLUE', 'My Location');} else {

	if (($bunchOaddresses == null) && ($myDatabase == null) && ($map_info == null)) {
		$map->addAddress('~15,-42', '3', 'CENTER', 'CENTER');
	}
}



if ($map_name <> null) {
    echo "
	<script type='text/javascript'>
	$('#mapTitle header').text('" . $map_name . "');
	$('#mapTitleField').val('" . $map_name . "')
	</script>
	";
}


if ($map_description <> null) {
    echo "
	<script type='text/javascript'>
	$('#mapDescriptionField').html('" . $map_description . "');
    $('#mapDescriptionFieldSubmit').html('" . $map_description . "');
    $('#mapDescript').html('" . $map_description . "');
    $('#legendItems').show();
	$('#mainButtonContainer').hide();  
	$('#legendHeader .contract').show(); 
	$('#legendHeader .expand').hide();
	$('#mapTitle #expandClose .expand').show();
	$('#mapTitle #expandClose .contract').hide();
	$('#mapDescript').hide();
	</script>
	";
}








$map->showMap();

?>
<div id="printLegend"></div> 
        </div>
       
    </body>
 
</html>