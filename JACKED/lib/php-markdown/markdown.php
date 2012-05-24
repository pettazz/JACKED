<?php

	require 'lib/markdownify.php';
	require 'lib/php-markdown.php';

	class Markdown{

		
        /**
        * Convert a string of HTML to Markdown
        * 
        * @param String $html The HTML to be converted to Markdown
        * @return String The Markdown version of @html
        */
		public static function toMarkdown($html){
			$mdf = new Markdownify();
			return $mdf->parseString($html);
		}

        /**
        * Convert a string of Markdown to HTML
        * 
        * @param String $markdown The Markdown to be converted to HTML
        * @return String The HTML version of @markdown
        */
		public static function toHTML($markdown){
			return PHPMarkdown($markdown);

		}

	}

?>