<?php

	/**
	 * Copyright (c) 2008 Rob Tinsley (www.bitari.com)
	 *
	 * Permission is hereby granted, free of charge, to any person
	 * obtaining a copy of this software and associated documentation
	 * files (the "Software"), to deal in the Software without
	 * restriction, including without limitation the rights to use,
	 * copy, modify, merge, publish, distribute, sublicense, and/or sell
	 * copies of the Software, and to permit persons to whom the
	 * Software is furnished to do so, subject to the following
	 * conditions:
	 *
	 * The above copyright notice and this permission notice shall be
	 * included in all copies or substantial portions of the Software.
	 *
	 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
	 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
	 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
	 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
	 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
	 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
	 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
	 * OTHER DEALINGS IN THE SOFTWARE.
	 *
	 * @package	MarkovLetterChain
	 * @author	Rob Tinsley (www.bitari.com)
	 * @copyright	2008 Rob Tinsley
	 * @license	http://www.opensource.org/licenses/mit-license.php	MIT License
	 * @link	http://www.bitari.com/
	 */

	/*
	 * CHANGES:
	 *
	 * 2008-02-04	initial version
	 * 2008-02-05	documentation started
	 * 2008-02-07	renamed some variables, documentation updates
	 * 2008-02-15	renamed some variables, documentation updates
	 * 2008-02-21	unicode (UTF-8) enhancements
	 */

	/**
	 * @package	MarkovLetterChain
	 * @version	2008-02-21 (alpha)
	 */

	class MarkovLetterChain
	{
		/**
		 * The order of the chain, 1 for first order, 2 for second order, and so on.
		 *
		 * @var     integer
		 * @access  private
		 */
		var $_order;

		/**
		 * The frequency table, where _table[seed_string][next_letter] = frequency
		 *
		 * Note that there are two special letters, '^' and '$', which denote beginning and end of a word, respectively.
		 *
		 * @var     array
		 * @access  private
		 */
		var $_table = array();

		/**
		 * The list of words fed to the object, where _dictionary[word] = count
		 *
		 * @var     array
		 * @access  private
		 */
		var $_dictionary = array();

		/**
		 * The entropy of the last string returned from _generate() in bits
		 *
		 * @var     array
		 * @access  private
		 */
		var $_last_generated_entropy = 0;

		/**
		 * Whether to use UTF-8 (rather than ASCII) internally.
		 *
		 * @var     boolean
		 * @access  private
		 */
		var $_utf8_enabled = false;

			/* **************************************** */

		/**
		 * Constructor.
		 *
		 * @param   integer  $order (optional) the order of the chain, defaults to 1
		 * @return  void
		 * @access  public
		 */
		function MarkovLetterChain ( $order = 1, $load_source = false )
		{
			$this->_order = $order;
			if($load_source){
				$this->feed(file_get_contents(JACKED_LIB_ROOT . 'Markov-Source.txt'));
			}
		}

		/**
		 * Save the object's state to a file.
		 *
		 * It is optional whether or not the dictionary is saved as that might increase the file size substantially.
		 *
		 * @param   string   $filename the file to save the object state into
		 * @param   string   $filename the file to save the object state into
		 * @param   boolean  $save_dictionary (optional) whether to include the dictionary in the saved state, defaults to true
		 * @return  boolean  true, or false if there was an error
		 * @access  public
		 */
		function save_state ( $filename, $save_dictionary = true )
		{
			$f = @fopen( $filename, 'w' );
			if ( !is_resource( $f ) ) {
				return false;
			}
			$serial = serialize( array(
				'order' => $this->_order,
				'table' => $this->_table,
				'dictionary' => $save_dictionary ? $this->_dictionary : array()
			) );
			$bytes = @fwrite( $f, $serial );
			@fclose( $f );
			return $bytes === strlen( $serial );
		}

		/**
		 * Load the object's state from a file.
		 *
		 * It is optional whether or not the dictionary is loaded as that might increase memory usage substantially.
		 * Note that if the dictionary is not loaded (or you are loading from a state file to which the dictionary was not saved)
		 * the generate() function will not be able to filter out dictionary words.
		 *
		 * @param   string   $filename the file to save the object state into
		 * @param   boolean  $load_dictionary (optional) whether to include the dictionary in the loaded state, defaults to true
		 * @return  boolean  true, or false if there was an error
		 * @access  public
		 */
		function load_state ( $filename, $load_dictionary = true )
		{
			$this->_order = 0;
			$this->_table = array();
			$this->_dictionary = array();
			$this->_last_generated_entropy = 0;
			$this->_utf8_enabled = false;

			$file_contents_array = file( $filename );
			if ( !is_array( $file_contents_array ) ) {
				return false;
			}
			$file_contents = implode( '', $file_contents_array );
			unset( $file_contents_array );
			$unserial = unserialize( $file_contents );
			unset( $file_contents );
			if ( !is_array( $unserial ) ) {
				return false;
			}

			$this->_order = $unserial['order'];
			$this->_table = $unserial['table'];
			$this->_dictionary = $load_dictionary ? $unserial['dictionary'] : array();

			return true;
		}

		/**
		 * Print the frequency table on stdout.
		 *
		 * @return  void
		 * @access  public
		 */
		function print_table ()
		{
			foreach ( array_keys( $this->_table ) as $seed ) {
				foreach ( $this->_table[$seed] as $letter => $freq ) {
					if ( $letter !== '#sum' ) {
						print "$seed|$letter\t$freq\n";
					}
				}
			}
		}

		/**
		 * Print the dictionary on stdout.
		 *
		 * @return  void
		 * @access  public
		 */
		function print_dictionary ()
		{
			foreach ( $this->_dictionary as $word => $freq ) {
				print "$word\t$freq\n";
			}
		}

		/**
		 * Whether a particular word is in the dictionary.
		 *
		 * @param   string   $word the word to look for in the dictionary
		 * @return  boolean  true if the word is in the dictionary, otherwise false
		 * @access  public
		 */
		function in_dictionary ( $word )
		{
			return array_key_exists( $word, $this->_dictionary );
		}

			/* **************************************** */

		/**
		 * Whether to work in UTF-8 rather than ASCII
		 *
		 * @param   mixed    $enable true to enable, false to disable, null to make no change
		 * @return  boolean  true if enabled before the function was called, otherwise false
		 * @access  public
		 */
		function enable_utf8 ( $enable = true )
		{
			$old = $this->_utf8_enabled;
			if ( $enable !== NULL ) {
				$this->_utf8_enabled = ( $enable ? true : false );
			}
			return $old;
		}

		/**
		 * Get string length (using UTF-8 functions if appropriate)
		 *
		 * @param  string   $string the string being measured for length
		 * @return integer  the length of the string on success, and 0 if the string is empty
		 * @access private
		 */
		function _strlen ( $string )
		{
			if ( $this->_utf8_enabled ) {
				return mb_strlen( $string, 'UTF-8' );
			} else {
				return strlen( $string );
			}
		}

		/**
		 * Return part of a string (using UTF-8 functions if appropriate)
		 *
		 * @param  string   $string the input string
		 * @param  integer  $start the starting position, counting from 0
		 * @param  integer  $length (optional) the maximum length of the string to return, defaults to entire string
		 * @return integer  the length of the string on success, and 0 if the string is empty
		 * @access private
		 *
		 */
		function _substr ( $string, $start, $length = NULL )
		{
			if ( $this->_utf8_enabled ) {
				if ( $length !== NULL ) {
					$r = mb_substr( $string, $start, $length, 'UTF-8' );
				} else {
					$r = mb_substr( $string, $start, mb_strlen( $string ), 'UTF-8' );
				}
			} else {
				if ( $length !== NULL ) {
					$r = substr( $string, $start, $length );
				} else {
					$r = substr( $string, $start );
				}
			}
			return is_string( $r ) ? $r : '';
		}

		/**
		 * Add more words to the frequency table and the dictionary.
		 *
		 * @param   string   $stuff a free-form string
		 * @param   string   $charset (optional) the character set of the input string
		 * @return  void
		 * @access  public
		 */
		function feed ( $stuff, $charset = NULL )
		{
			if ( $this->_utf8_enabled ) {
				if ( $charset !== NULL ) {
					$stuff = iconv( $charset, 'UTF-8//TRANSLIT', $stuff );
				}
				$stuff = mb_strtolower( $stuff, 'UTF-8' );
				preg_match_all( "/(?<=[^'\p{L}_\p{N}.]|\s')[\p{L}]+(?=[^'\p{L}_\p{N}.]|\.[^\p{L}_\p{N}])/iu", " $stuff ", $words );
			} else {
				if ( $charset !== NULL ) {
					$stuff = iconv( $charset, 'ASCII//TRANSLIT', $stuff );
				}
				$stuff = strtolower( $stuff );
				preg_match_all( "/(?<=[^'a-z_0-9.]|\s')[a-z]+(?=[^'a-z_0-9.]|\.[^a-z_0-9])/i", " $stuff ", $words );
			}
			unset( $stuff );

			foreach ( $words[0] as $word ) {
				$this->_word( $word );
			}
		}

		/**
		 * Add one word to the frequency table and (optionally) the dictionary.
		 *
		 * @param   string   $word the word to add to the frequency table
		 * @param   boolean  $add_to_dictionary (optional) whether to also add the word to the dictionary, defaults to true
		 * @return  void
		 * @access  private
		 */
		function _word ( $word, $add_to_dictionary = true )
		{
			if ( !is_integer( $this->_order ) || $this->_order < 1 ) {
				return;
			}

			if ( !$add_to_dictionary ) {
				// do nothing
			} elseif ( array_key_exists( $word, $this->_dictionary ) ) {
				$this->_dictionary[$word]++;
			} else {
				$this->_dictionary[$word] = 1;
			}

			$word = '^' . $word . '$';	# mark the beginning and end of $word
			$len = $this->_strlen( $word );

			// process all substrings at the beginning of $word shorter than $_order
			for ( $leadin = 2; $leadin <= $this->_order && $leadin <= $len; $leadin++ ) {
				$this->_segment( $this->_substr( $word, 0, $leadin ) );
			}

			// process all substring of $word with length $_order
			for ( $cursor = 0; $cursor < $len - $this->_order; $cursor++ ) {
				$this->_segment( $this->_substr( $word, $cursor, $this->_order + 1 ) );
			}
		}

		/**
		 * Add part of a word to the frequency table.
		 *
		 * @param   string   $segment the word-segment to add to the frequency table
		 * @return  void
		 * @access  private
		 */
		function _segment ( $segment )
		{
			$s0 = $this->_substr( $segment,  0, -1 );
			$s1 = $this->_substr( $segment, -1,  1 );
			unset( $segment );

			if ( array_key_exists( $s0, $this->_table ) && array_key_exists( $s1, $this->_table[$s0] ) ) {
				$this->_table[$s0][$s1]++;
			} else {
				$this->_table[$s0][$s1] = 1;
			}

			if ( array_key_exists( $s0, $this->_table ) && array_key_exists( '#sum', $this->_table[$s0] ) ) {
				$this->_table[$s0]['#sum']++;
			} else {
				$this->_table[$s0]['#sum'] = 1;
			}
		}

			/* **************************************** */

		/**
		 * Makes all frequencies in the frequency table be their own root.
		 *
		 * @param   float    $n (optional) the root to use, defaults to 2 (square root)
		 * @return  void
		 * @access  public
		 */
		function root ( $n = 2 )
		{
			$this->_freq_formula( 'root', $n );
		}

		/**
		 * Multiplies all frequencies in the frequency table by a constant.
		 *
		 * @param   float   $n the constant to muliply the frequencies by
		 * @return  void
		 * @access  public
		 */
		function multiply ( $n )
		{
			$this->_freq_formula( 'multiply', $n );
		}

		/**
		 * Drops all frequency table entries with a frequency below a given threshold.
		 *
		 * @param   float    $n the threshold for all frequencies
		 * @return  void
		 * @access  public
		 */
		function threshold ( $n )
		{
			$this->_freq_formula( 'threshold', $n );
		}

		/**
		 * Drops all frequency table entries with a frequency below a given threshold.
		 *
		 * @param   string   $f the name of the formula
		 * @param   mixed    $n (optional) a constant also passed to the formula
		 * @return  void
		 * @access  private
		 */
		function _freq_formula ( $f, $n = NULL )
		{
			foreach ( array_keys( $this->_table ) as $seed ) {
				unset( $this->_table[$seed]['#sum'] );
				foreach ( $this->_table[$seed] as $letter => $freq ) {
					switch ( $f ) {
					case 'root':
						if ( $n === 2 ) {
							$freq = sqrt( $freq );
						} else {
							$freq = pow( $freq, 1 / $n );
						}
						break;
					case 'multiply':
						$freq *= $n;
						break;
					case 'threshold':
						if ( $freq < $n ) {
							$freq = 0;
						}
						break;
					}
					$freq = intval( $freq + 0.5 );
					if ( $freq > 0 ) {
						$this->_table[$seed][$letter] = $freq;
					} else {
						unset( $this->_table[$seed][$letter] );
					}
				}
				if ( count( $this->_table[$seed] ) ) {
					$this->_table[$seed]['#sum'] = array_sum( $this->_table[$seed] );
				} else {
					unset( $this->_table[$seed] );
				}
			}
		}

			/* **************************************** */

		/**
		 * Generate a random word.
		 *
		 * @param   integer  $minlen the shortest allowed word
		 * @param   integer  $maxlen the longest allowed word
		 * @param   boolean  $allow_dictionary_words (optional) whether to allow the function to return dictionary words, defaults to true
		 * @return  mixed    NULL if there was an error, otherwise a string
		 * @access  public
		 */
		function generate ( $minlen, $maxlen, $allow_dictionary_words = true )
		{
			for ($i = 0; $i < 100; $i++) {
				$word = $this->_generate();
				if ( !is_string( $word ) ) {
					return NULL;
				}
				if ( !$allow_dictionary_words && array_key_exists( $word, $this->_dictionary ) ) {
					continue;
				}
				$wordlen = $this->_strlen( $word );
				if ( $wordlen >= $minlen && $wordlen <= $maxlen ) {
					return $word;
				}
			}
			return NULL;
		}

		/**
		 * Generate a random word.
		 *
		 * @return  mixed    NULL if there was an error, otherwise a string
		 * @access  private
		 * @uses    MarkovLetterChain::rand()
		 */
		function _generate ()
		{
			$this->_last_generated_entropy = 0;
			$k = 1.0;

			if ( !is_integer( $this->_order ) || $this->_order < 1 ) {
				return NULL;
			}

			$word = '';
			$seed = '^';
			while (true) {
				$sum = $this->_table[$seed]['#sum'];
				$r = $this->rand( 0, $sum - 1 );
				foreach ( $this->_table[$seed] as $letter => $freq ) {
					if ( $letter === '#sum' ) {
						continue;
					}
					if ( $r < $freq ) {
						break;
					}
					$r -= $freq;
				}

				$k *= $sum / $freq;
				if ($letter == '$') {
					$this->_last_generated_entropy = log( $k, 2 );
					return $word;
				}

				$word .= $letter;
				$seed .= $letter;
				if ( $this->_strlen( $seed ) > $this->_order ) {
					$seed = $this->_substr( $seed, -$this->_order, $this->_order );
				}
			}
		}

		/**
		 * Generate a random integer.
		 *
		 * @param   integer  $min the lowest value to return
		 * @param   integer  $max the highest value to return
		 * @return  integer  A (pseudo-)random value in the range [min,max]
		 * @access  private
		 */
		function rand ( $min, $max )
		{
			return rand( $min, $max );
		}

		/**
		 * Calculate the entropy of the last string returned from _generate() in bits.
		 *
		 * IMPORTANT NOTE: the entropy of the string returned from generate()
		 * [without the leading underscore] will be lower, as that function
		 * imposes additional constraints, such as on the length of the word,
		 * and (optionally) excludes dictionary words.
		 *
		 * @return  float    the entropy of the last string returned from _generate() in bits
		 * @access  public
		 */
		function last_generated_entropy ()
		{
			return $this->_last_generated_entropy;
		}

	}

?>
