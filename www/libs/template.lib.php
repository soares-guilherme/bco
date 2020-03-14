<?php
define("T_BYFILE",              0);
define("T_BYVAR",               1);
define("TP_ROOTBLOCK",    '_ROOT');

class TemplatePowerParser
{
  var $tpl_base;
  var $tpl_include;
  var $tpl_count;
  var $parent   = Array();
  var $defBlock = Array();
  var $rootBlockName;
  var $ignore_stack;
  var $version;
   function __construct( $tpl_file, $type )
   {
       $this->version        = '3.0.2';
       $this->tpl_base       = Array( $tpl_file, $type );
       $this->tpl_count      = 0;
	     $this->ignore_stack   = Array( false );
   }
   function __errorAlert( $message )
   {
       print( '<br>'. $message .'<br>'."\r\n");
   }
   function __prepare()
   {
       $this->defBlock[ TP_ROOTBLOCK ] = Array();
       $tplvar = $this->__prepareTemplate( $this->tpl_base[0], $this->tpl_base[1]  );
       $initdev["varrow"]  = 0;
       $initdev["coderow"] = 0;
       $initdev["index"]   = 0;
       $initdev["ignore"]  = false;
       $this->__parseTemplate( $tplvar, TP_ROOTBLOCK, $initdev );
       $this->__cleanUp();
   }
    function __cleanUp()
    {
        for( $i=0; $i <= $this->tpl_count; $i++ )
        {
            $tplvar = 'tpl_rawContent'. $i;
            unset( $this->{$tplvar} );
        }
    }
    function __prepareTemplate( $tpl_file, $type )
    {
        $tplvar = 'tpl_rawContent'. $this->tpl_count;
        if( $type == T_BYVAR )
        {
            $this->{$tplvar}["content"] = preg_split("/\n/", $tpl_file, -1, PREG_SPLIT_DELIM_CAPTURE);
        }
        else
        {
            $this->{$tplvar}["content"] = @file( $tpl_file ) or
                die( $this->__errorAlert('TemplatePower Error: Couldn\'t open [ '. $tpl_file .' ]!'));
        }

        $this->{$tplvar}["size"]    = sizeof( $this->{$tplvar}["content"] );
        $this->tpl_count++;
        return $tplvar;
    }
    function __parseTemplate( $tplvar, $blockname, $initdev )
    {
        $coderow = $initdev["coderow"];
        $varrow  = $initdev["varrow"];
        $index   = $initdev["index"];
        $ignore  = $initdev["ignore"];
        while( $index < $this->{$tplvar}["size"] )
        {
            if ( preg_match('/<!--[ ]?(START|END) IGNORE -->/', $this->{$tplvar}["content"][$index], $ignreg) )
            {
                if( $ignreg[1] == 'START')
                {
					          array_push( $this->ignore_stack, true );
                }
                else
                {
					          array_pop( $this->ignore_stack );
                }
            }
            else
            {
                if( !end( $this->ignore_stack ) )
                {
                    if( preg_match('/<!--[ ]?(START|END|INCLUDE|INCLUDESCRIPT|REUSE) BLOCK : (.+)-->/', $this->{$tplvar}["content"][$index], $regs))
                    {
                        $regs[2] = trim( $regs[2] );
                        if( $regs[1] == 'INCLUDE')
                        {
                            $include_defined = true;
                            if( isset( $this->tpl_include[ $regs[2] ]) )
                            {
                                $tpl_file = $this->tpl_include[ $regs[2] ][0];
                                $type   = $this->tpl_include[ $regs[2] ][1];
                            }
                            else
                            if (file_exists( $regs[2] ))
                            {
                                $tpl_file = $regs[2];
                                $type     = T_BYFILE;
                            }
                            else
                            {
                                $include_defined = false;
                            }

                            if( $include_defined )
                            {
                                $initdev["varrow"]  = $varrow;
                                $initdev["coderow"] = $coderow;
                                $initdev["index"]   = 0;
                                $initdev["ignore"]  = false;
                                $tplvar2 = $this->__prepareTemplate( $tpl_file, $type );
                                $initdev = $this->__parseTemplate( $tplvar2, $blockname, $initdev );
                                $coderow = $initdev["coderow"];
                                $varrow  = $initdev["varrow"];
                            }
                        }
                        else
                        if( $regs[1] == 'INCLUDESCRIPT' )
                        {
                            $include_defined = true;
						    if( isset( $this->tpl_include[ $regs[2] ]) )
                            {
                                $include_file = $this->tpl_include[ $regs[2] ][0];
								                $type         = $this->tpl_include[ $regs[2] ][1];
                            }
                            else
                            if (file_exists( $regs[2] ))
                            {
                                $include_file = $regs[2];
								                $type         = T_BYFILE;
                            }
                            else
                            {
                                $include_defined = false;
                            }
                            if( $include_defined )
                            {
                                ob_start();
                                
								                if( $type == T_BYFILE )
								                {
                                    if( !@include_once( $include_file ) )
                                    {
                                        $this->__errorAlert( 'TemplatePower Error: Couldn\'t include script [ '. $include_file .' ]!' );
										                    exit();
                                    }
								                }
								                else
								                {
								                    eval( "?>" . $include_file );
								                }

                                $this->defBlock[$blockname]["_C:$coderow"] = ob_get_contents();
                                $coderow++;

                                ob_end_clean();
                            }
                        }
                        else
                        if( $regs[1] == 'REUSE' )
                        {
                            if (preg_match('/(.+) AS (.+)/', $regs[2], $reuse_regs))
                            {
                                $originalbname = trim( $reuse_regs[1] );
                                $copybname     = trim( $reuse_regs[2] );
                                if (isset( $this->defBlock[ $originalbname ] ))
                                {
                                    $this->defBlock[ $copybname ] = $this->defBlock[ $originalbname ];
                                    $this->defBlock[ $blockname ]["_B:". $copybname ] = '';
                                    $this->index[ $copybname ]  = 0;
                                    $this->parent[ $copybname ] = $blockname;
                                }
                                else
                                {
                                    $this->__errorAlert('TemplatePower Error: Can\'t find block \''. $originalbname .'\' to REUSE as \''. $copybname .'\'');
                                }
                            }
                            else
                            {
                                $this->defBlock[$blockname]["_C:$coderow"] = $this->{$tplvar}["content"][$index];
                                $coderow++;
                            }
                        }
                        else
                        {
                            if( $regs[2] == $blockname )
                            {
                                break;
                            }
                            else
                            {
                                $this->defBlock[ $regs[2] ] = Array();
                                $this->defBlock[ $blockname ]["_B:". $regs[2]] = '';
                                $this->index[ $regs[2] ]  = 0;
                                $this->parent[ $regs[2] ] = $blockname;
                                $index++;
                                $initdev["varrow"]  = 0;
                                $initdev["coderow"] = 0;
                                $initdev["index"]   = $index;
                                $initdev["ignore"]  = false;
                                $initdev = $this->__parseTemplate( $tplvar, $regs[2], $initdev );
                                $index = $initdev["index"];
                            }
                        }
                    }
                    else
                    {
                        $sstr = explode( '{', $this->{$tplvar}["content"][$index] );
						            reset( $sstr );
                        if (current($sstr) != '')
                        {
                            $this->defBlock[$blockname]["_C:$coderow"] = current( $sstr );
                            $coderow++;
                        }

                        while (next($sstr))
                        {
                            $pos = strpos( current($sstr), "}" );

                            if ( ($pos !== false) && ($pos > 0) )
                            {
                                $strlength = strlen( current($sstr) );
                                $varname   = substr( current($sstr), 0, $pos );

                                if (strstr( $varname, ' ' ))
                                {
                                    $this->defBlock[$blockname]["_C:$coderow"] = '{'. current( $sstr );
                                    $coderow++;
                                }
                                else
                                {
                                    $this->defBlock[$blockname]["_V:$varrow" ] = $varname;
                                    $varrow++;
                                    if( ($pos + 1) != $strlength )
                                    {
                                        $this->defBlock[$blockname]["_C:$coderow"] = substr( current( $sstr ), ($pos + 1), ($strlength - ($pos + 1)) );
                                        $coderow++;
                                    }
                                }
                            }
                            else
                            {
                                $this->defBlock[$blockname]["_C:$coderow"] = '{'. current( $sstr );
                                $coderow++;
                            }
                        }
                    }
                }
                else
                {
                    $this->defBlock[$blockname]["_C:$coderow"] = $this->{$tplvar}["content"][$index];
                    $coderow++;
                }
            }

            $index++;
        }

        $initdev["varrow"]  = $varrow;
        $initdev["coderow"] = $coderow;
        $initdev["index"]   = $index;

        return $initdev;
    }
    function version()
    {
        return $this->version;
    }
    function assignInclude( $iblockname, $value, $type=T_BYFILE )
    {
        $this->tpl_include["$iblockname"] = Array( $value, $type );
    }
}

class TemplatePower extends TemplatePowerParser
{
  var $index    = Array();
  var $content  = Array();       
  var $currentBlock;
  var $showUnAssigned;
  var $serialized;
  var $globalvars = Array();
  var $prepared;
    function __construct( $tpl_file='', $type= T_BYFILE )
    {
        parent::__construct( $tpl_file, $type );

        $this->prepared       = false;
        $this->showUnAssigned = false;
		    $this->serialized     = false;
    }
    function __deSerializeTPL( $stpl_file, $type )
    {
        if( $type == T_BYFILE )
        {
            $serializedTPL = @file( $stpl_file ) or
                die( $this->__errorAlert('TemplatePower Error: Can\'t open [ '. $stpl_file .' ]!'));
        }
        else
        {
            $serializedTPL = $stpl_file;
        }

        $serializedStuff = unserialize( join ('', $serializedTPL) );

        $this->defBlock = $serializedStuff["defBlock"];
        $this->index    = $serializedStuff["index"];
        $this->parent   = $serializedStuff["parent"];
    }
    function __makeContentRoot()
    {
        $this->content[ TP_ROOTBLOCK ."_0"  ][0] = Array( TP_ROOTBLOCK );
        $this->currentBlock = &$this->content[ TP_ROOTBLOCK ."_0" ][0];
    }
    function __assign( $varname, $value)
    {
        if( sizeof( $regs = explode('.', $varname ) ) == 2 )
		        {
	          $ind_blockname = $regs[0] .'_'. $this->index[ $regs[0] ];		
            $lastitem = sizeof( $this->content[ $ind_blockname ] );
            $lastitem > 1 ? $lastitem-- : $lastitem = 0;
            $block = &$this->content[ $ind_blockname ][ $lastitem ];
            $varname = $regs[1];
        }
        else
        {
            $block = &$this->currentBlock;
        }

        $block["_V:$varname"] = $value;

    }
    function __assignGlobal( $varname, $value )
    {
        $this->globalvars[ $varname ] = $value;
    }
    function __outputContent( $blockname )
    {
        $numrows = sizeof( $this->content[ $blockname ] );

        for( $i=0; $i < $numrows; $i++)
        {
            $defblockname = $this->content[ $blockname ][$i][0];

            for( reset( $this->defBlock[ $defblockname ]);  $k = key( $this->defBlock[ $defblockname ]);  next( $this->defBlock[ $defblockname ] ) )
            {
                if ($k[1] == 'C')
                {
                    print( $this->defBlock[ $defblockname ][$k] );
                }
                else
                if ($k[1] == 'V')
                {
                    $defValue = $this->defBlock[ $defblockname ][$k];

                    if( !isset( $this->content[ $blockname ][$i][ "_V:". $defValue ] ) )
                    {
                        if( isset( $this->globalvars[ $defValue ] ) )
                        {
                            $value = $this->globalvars[ $defValue ];
                        }
                        else
                        {
                            if( $this->showUnAssigned )
                            {
                                $value = '{'. $defValue .'}';
                            }
                            else
                            {
                                $value = '';
                            }
                        }
                    }
                    else
                    {
                        $value = $this->content[ $blockname ][$i][ "_V:". $defValue ];
                    }

                    print( $value );

                }
                else
                if ($k[1] == 'B')
                {
                    if( isset( $this->content[ $blockname ][$i][$k] ) )
                    {
                        $this->__outputContent( $this->content[ $blockname ][$i][$k] );
                    }
                }
            }
        }
    }

    function __printVars()
    {
        var_dump($this->defBlock);
        print("<br>--------------------<br>");
        var_dump($this->content);
    }
    function serializedBase()
    {
        $this->serialized = true;
        $this->__deSerializeTPL( $this->tpl_base[0], $this->tpl_base[1] );
    }
    function showUnAssigned( $state = true )
    {
        $this->showUnAssigned = $state;
    }
    function prepare()
    {
        if (!$this->serialized)
        {
            parent::__prepare();
        }
        $this->prepared = true;
        $this->index[ TP_ROOTBLOCK ]    = 0;
        $this->__makeContentRoot();
    }
    function newBlock( $blockname )
    {
        $parent = &$this->content[ $this->parent[$blockname] .'_'. $this->index[$this->parent[$blockname]] ];

		    $lastitem = sizeof( $parent );
        $lastitem > 1 ? $lastitem-- : $lastitem = 0;
		    $ind_blockname = $blockname .'_'. $this->index[ $blockname ];		
        if ( !isset( $parent[ $lastitem ]["_B:$blockname"] ))
        {
            $this->index[ $blockname ] += 1;
            $ind_blockname = $blockname .'_'. $this->index[ $blockname ];			
            if (!isset( $this->content[ $ind_blockname ] ) )
            {
                 $this->content[ $ind_blockname ] = Array();
            }
            $parent[ $lastitem ]["_B:$blockname"] = $ind_blockname;
        }
        $blocksize = sizeof( $this->content[ $ind_blockname ] );
        $this->content[ $ind_blockname ][ $blocksize ] = Array( $blockname );
        $this->currentBlock = &$this->content[ $ind_blockname ][ $blocksize ];
    }
    function assignGlobal( $varname, $value='' )
    {
        if (is_array( $varname ))
        {
            foreach($varname as $var => $value)
            {
				$value = utf8_encode_once($value);
                $this->__assignGlobal( $var, $value );
            }
        }
        else
        {
			$value = utf8_encode_once($value);
            $this->__assignGlobal( $varname, $value );
        }
    }
    function assign( $varname, $value='', $encode=true )
    {
        if (is_array( $varname ))
        {
            foreach($varname as $var => $value)
            {
				if($encode)
					$value = utf8_encode_once($value);
                $this->__assign( $var, $value );
            }
        }
        else
        {
			if($encode)
				$value = utf8_encode_once($value);
            $this->__assign( $varname, $value );
        }
    }
    function gotoBlock( $blockname )
    {
        if ( isset( $this->defBlock[ $blockname ] ) )
        {
		    $ind_blockname = $blockname .'_'. $this->index[ $blockname ];
            $lastitem = sizeof( $this->content[ $ind_blockname ] );
            $lastitem > 1 ? $lastitem-- : $lastitem = 0;
            $this->currentBlock = &$this->content[ $ind_blockname ][ $lastitem ];
        }
    }
    function getVarValue( $varname )
    {
        if( sizeof( $regs = explode('.', $varname ) ) == 2 )
        {
		        $ind_blockname = $regs[0] .'_'. $this->index[ $regs[0] ];
			
            $lastitem = sizeof( $this->content[ $ind_blockname ] );
            $lastitem > 1 ? $lastitem-- : $lastitem = 0;
            $block = &$this->content[ $ind_blockname ][ $lastitem ];
            $varname = $regs[1];
        }
        else
        {
            $block = &$this->currentBlock;
        }
        return $block["_V:$varname"];
    }
    function printToScreen()
    {
        if ($this->prepared)
        {
            $this->__outputContent( TP_ROOTBLOCK .'_0' );
        }
        else
        {
            $this->__errorAlert('TemplatePower Error: Template isn\'t prepared!');
        }
    }
    function getOutputContent()
    {
        ob_start();
        $this->printToScreen();
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }
}
?>
