
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>File content scanner</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
	
    <style type="text/css">
	html,body {
		padding: 0;
		margin:  0;
	}
	
	header {
		background: #161616;
		padding: 10px;
		box-shadow: black 0px 0px 5px;
	}
	
	.page {
		padding: 20px 10px 10px 10px;
		font-family: Arial;
		
	}
	
	.page h1 {
		margin: 0 0 10px;
		font: bold 22px Arial;
	}
	
	header h1 {
		font: 26px Arial;
		color: white;
		margin: 0;
		display: inline-block;
	}
	
	.page textarea {
		padding: 5px;
		border: solid 1px #CCCCCC;
		border-radius: 3px;
		font: 12px/20px Courier New;
		width: 99%;
		height: 150px;
	}
	
	.page button {
		width: 200px;
		font: bold 12px Arial;
		margin: 15px auto 0px auto;
		cursor: pointer;
		padding: 10px;
	} 
	
	header h2 {
		font: 16px Arial;
		color: #CCCCCC;
		margin: 0;
		display: inline-block;
		margin-left: 10px;
	}
	
	.match {
		background: yellow;
		font: bold 14px Arial;
		padding: 10px;
	}
	
	.detail {
		background: #EEEEEE;
		font: 12px/20px Courier New;
		padding: 10px;
		margin-bottom: 20px;
	}
	
	.hightlight {
		background: #FFE65E;
		font: bold 12px/20px Courier New;
	}
	
	label {
		font-size: 12px;
		margin-right: 10px;
	}
	</style>
	
	<script type="text/javascript">
		function lockControl( el ) {
			//el.setAttribute( 'disabled', 'true' );
			//el.setAttribute( 'disabled', 'false' );
			el.value = 'Searching in progress...';
		}
	</script>
  </head>
  <body>
	<header>
		<h1>File content scanner</h1>
		<h2>&copy; ABlack, 2013</h2>
	</header>
	
	<div class="page">
	<form id="form" method="post">
	<h1>Search:</h1>
	<textarea name="query" placeholder="Type your query here..."><?php print $_POST[ 'query' ]; ?></textarea>
	<button>Start searching</button>
	<label><input type="checkbox" name="whole_word_only" /> Match whole query only</label>
	<label><input type="checkbox" name="case_ignore" /> Ignore case</label>
	<label><input type="checkbox" name="fast_mode" /> Stop on fetching first match in file <i>[fast mode]</i></label>
	</form>
	<?php
		if( !empty( $_POST[ 'query' ] ) ) {
			
		class scanner {
			public static $matches = 0;
			public static $output = "";
			
			public static function dirscanner( $dir ) {
				$q = $_POST[ 'query' ];
				$files = scandir( $dir );
				foreach( $files as $file ) {
					$ignore = array( '.', '..' );
					if( !in_array( $file, $ignore ) && !is_dir( $dir . '\\' . $file ) ) {
						if( filesize( $dir . '/' . $file ) < 1024 * 8 * 1024 ) {
							$contents = file_get_contents( $dir . '/' . $file );
							//print $contents;
							if( $_POST[ 'case_ignore' ] ) { $q = strtolower( $q ); $contents = strtolower( $contents ); }
							if( strpos( $contents, $q ) ) {
								$dataParts = explode( $q, htmlspecialchars( $contents ) );
								$counter = count( $dataParts ) - 1;
								for( $x = 0; $x <= $counter; $x++ ) {
									$dataPart1 = substr( $dataParts[ $x ], strlen( $dataParts ) - 200 );
									$dataPart2 = substr( $dataParts[ $x + 1 ], 0, 200 );
									
									$prevLetter = substr( $dataParts[ $x ], strlen( $dataParts ) - 1 );
									$nextLetter = substr( $dataParts[ $x + 1 ], 0, 1 );
									
									if( @$_POST[ 'whole_word_only' ] ) {
										$allow = array( ' ', "\r", "\n", "\r\n" );
										if( !in_array( $prevLetter, $allow ) || !in_array( $nextLetter, $allow ) ) continue;
									}
									
									$line = substr_count( $dataParts[ 0 ], "\r" ) + 1;
									self::$matches ++;
									self::$output .= '<div class="match">'.$dir . '\\' . $file.', line: '.$line.'</div><div class="detail">'.($dataPart1.'<span class="hightlight">'.$q.'</span>'.$dataPart2).'</div>';
									if( $_POST[ 'fast_mode' ] ) break;
								}
							}
						}
					} elseif( !in_array( $file, $ignore ) && is_dir( $dir . '/' . $file ) ) {
						scanner::dirscanner( $dir . '\\' . $file );
					}
				}
			}
		}
		
		scanner::dirscanner( dirname( __FILE__ ) );
	?>
		<br />
		<h1>Result: found <?php print scanner::$matches; ?> matches</h1>
		<?php print scanner::$output; ?>
	<?php
		}
	?>
	</div>
  </body>
</html>