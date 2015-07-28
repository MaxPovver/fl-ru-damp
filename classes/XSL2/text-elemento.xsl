<?xml version="1.0" encoding="ISO-8859-1" ?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:svg="http://www.w3.org/2000/svg"
    xmlns:v="urn:schemas-microsoft-com:vml"
    xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"             
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"               
    xmlns:xs="http://www.w3.org/2001/XMLSchema"
    xmlns="http://www.w3.org/1999/xhtml"
    xmlns:xlink="http://www.w3.org/1999/xlink"
>

<!-- INDEX:
   * template:
        - svg-text
        - svg-tspan
        - svg-tref
        - svg-textPath
        - svg-altGlyph
    * match:
        - svg:text
-->


<!-- ********************************************************************************* -->
<!-- ********************************************************************************* -->
<!-- ********************************* ELEMENTO TEXT ********************************* -->
<!-- ********************************************************************************* -->
<!-- ********************************************************************************* -->

<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<xsl:template name="svg-text">
    <xsl:param name="tspan"><xsl:text>no</xsl:text></xsl:param>
    <xsl:param name="tref"><xsl:text>no</xsl:text></xsl:param>
    <xsl:param name="tpath"><xsl:text>no</xsl:text></xsl:param>
    <xsl:param name="external-path" />
    <xsl:param name="tref-string"><xsl:text></xsl:text></xsl:param>
    <xsl:param name="shift-x"><xsl:text>0</xsl:text></xsl:param>
    <xsl:param name="shift-y"><xsl:text>0</xsl:text></xsl:param>
    <xsl:param name="add-id"><xsl:text></xsl:text></xsl:param>
    
<!-- template chiamato per gestire i testi, richiamato da text o da
     tspan, tref, textpath -->


<!-- assegno un id unico ad ogni porzione di testo (elemento text o tspan, ecc.)
-->
<xsl:variable name="shape-id-text">
    <xsl:text>text</xsl:text><xsl:value-of select="count(preceding::svg:*) + 
                                                   count(ancestor::svg:*)" />
    <xsl:choose>
        <xsl:when test="$tpath = 'yes'">
            <xsl:text>tpath</xsl:text>
        </xsl:when>
        <xsl:when test="$tspan = 'yes'">
            <xsl:text>tspan</xsl:text>
        </xsl:when>
        <xsl:when test="$tref = 'yes'">
            <xsl:text>tref</xsl:text>
        </xsl:when>
        <xsl:otherwise>
            <xsl:text>text</xsl:text>
        </xsl:otherwise>
    </xsl:choose>
    <xsl:value-of select="$add-id" />
</xsl:variable>

<!-- valore di font-size dell'elemento corrente -->
<xsl:variable name="font-s">
    <xsl:call-template name="valore-font-size" />
</xsl:variable>

<!-- valore di font-weight dell'elemento corrente -->
<xsl:variable name="font-w">
    <xsl:call-template name="font-w-perc">
        <xsl:with-param name="font-w">
            <xsl:call-template name="valore-font-weight" />
        </xsl:with-param>
    </xsl:call-template> 
</xsl:variable>

<!-- valore di aggiustamento in base al tipo di font:
        dovuto al fatto che per posizionare i testi si calcola la lunghezza
        dei testi precedenti, moltiplicata per la dimensione del font. 
        Per un approssimazione migliore serve un aggiustamente, perchè, la 
        dimensione dei caratteri dipende anche dal tipo di caratteri, una m
        occupa più spazio di una i.
        Questi aggiustamenti sono diversi in base al tipo di font.
-->
<xsl:variable name="div-ff">
    <xsl:call-template name="divisione-font-family" />
</xsl:variable>

<!-- valore della dimensione del font, in base a font-size, font-weight e 
     al valore dell'aggiustamento -->
<xsl:variable name="font-s-val">
    <xsl:value-of select="ceiling((($font-s div $div-ff) + 
                                   ($font-s div $div-ff * $font-w )))" />
</xsl:variable>


<xsl:variable name="testo">
    <xsl:variable name="temp">
        <xsl:apply-templates mode="vuoto"/>
    </xsl:variable>
    <xsl:call-template name="normalizza-spazi">
        <xsl:with-param name="stringa">
            <xsl:value-of select="$temp" />
        </xsl:with-param>
    </xsl:call-template>
</xsl:variable>

<xsl:choose>
    <!-- OOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOO -->
    <!-- OOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOO -->
    <!-- se tpath = yes template è stato chiamato da un elemento textPath -->
    <!-- OOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOO -->
    <!-- OOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOO -->
    <xsl:when test="$tpath = 'yes'">
            <!-- gestione elemento textpath: semplice, il path è passato come parametro,
                    quindi chiamo la funzione che gestisce (e crea) le porzioni di testo
            -->
            <xsl:call-template name="svg-text-shape">
            <xsl:with-param name="shift-x">
                <xsl:value-of select="$shift-x" />
            </xsl:with-param>
            <xsl:with-param name="shift-y">
                <xsl:value-of select="$shift-y" />
            </xsl:with-param>
            <xsl:with-param name="id">
                <xsl:value-of select="$shape-id-text" />
            </xsl:with-param>
            <xsl:with-param name="testo">
                <xsl:call-template name="normalizza-spazi">
                    <xsl:with-param name="stringa">
                        <xsl:value-of select="." />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:with-param>
            <xsl:with-param name="path">
                <xsl:value-of select="$external-path" />
            </xsl:with-param>
        </xsl:call-template>
    </xsl:when>
    <!-- OOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOO -->
    <!-- OOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOO -->
    <!-- se tref = yes template è stato chiamato da un elemento tref -->
    <!-- OOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOO -->
    <!-- OOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOO -->
    <xsl:when test="$tref = 'yes'">
        <!-- gestione tref: imposto il path e chiamo il template. In teoria il
                    posizionamento dovuto ad elementi precedenti è impostato tramite
                    shift-x.
        -->
        <xsl:variable name="path">
            <xsl:text>m 0,0 l </xsl:text>
            <xsl:value-of select="ceiling((string-length($tref-string) + 2) * $font-s-val)"/>
            <xsl:text>,0 e</xsl:text>
        </xsl:variable>
        <xsl:call-template name="svg-text-shape">
            <xsl:with-param name="shift-x">
                <xsl:value-of select="$shift-x" />
            </xsl:with-param>
            <xsl:with-param name="shift-y">
                <xsl:value-of select="$shift-y" />
            </xsl:with-param>
            <xsl:with-param name="id">
                <xsl:value-of select="$shape-id-text" />
            </xsl:with-param>
            <xsl:with-param name="testo">
                <xsl:value-of select="$tref-string" />
            </xsl:with-param>
            <xsl:with-param name="path">
                <xsl:value-of select="$path" />
            </xsl:with-param>
        </xsl:call-template>

    </xsl:when>
    <!-- OOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOO -->
    <!-- OOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOO -->
    <!-- OOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOO -->
    <!-- se tspan = no template è stato chiamato da un elemento text -->
    <!-- OOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOO -->
    <!-- OOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOO -->
    <!-- OOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOO -->
    <xsl:when test="$tspan = 'no'">
        <!-- gestione dell'elemento text -->
        
        <xsl:choose>
        <xsl:when test="svg:tspan | svg:tref"> 
            <!-- caso in cui ho text con all'interno tref o tspan: gestisco ogni
             porzione di text, precedente/successiva ad ogni tref/tspan -->    
    
            <xsl:variable name="num-elementi">        
                <xsl:value-of select="count(svg:tspan) + 
                                      count(svg:tref)" />
            </xsl:variable>

            <!-- considero ogni tref-tspan andando a selezionare la porzione
                 di testo che sta tra il tref-tspan precedente (o l'inizio di text
                 se sono nel primo tref-tspan) e il tref-tspan corrente.
                 Quando sono nell'ultimo, gestisco la porzione di testo rimanente.
            -->
            <xsl:for-each select="svg:tspan | svg:tref">
            
                    <!-- posizione dell'attuale tref-tspan -->
                    <xsl:variable name="pos">
                        <xsl:value-of select="count(preceding-sibling::svg:tspan) + 
                                              count(preceding-sibling::svg:tref)  + 1" />
                    </xsl:variable>
   
                    <!-- nome dell'elemento -->
                    <xsl:variable name="elemento">
                        <xsl:value-of select="name()" />
                    </xsl:variable>

                    <!-- nome dell'elemento precedente -->
                    <xsl:variable name="elemento-prec">
                        <xsl:choose>
                            <xsl:when test="$pos > 1 ">
                                <xsl:for-each select="preceding-sibling::svg:tspan |
                                                      preceding-sibling::svg:tref">
                                    <xsl:if test="position() = last()">
                                        <xsl:value-of select="name()" />
                                    </xsl:if>
                                </xsl:for-each>
                            </xsl:when>
                            <xsl:otherwise>
                                <xsl:text></xsl:text>
                            </xsl:otherwise>
                        </xsl:choose>
                    </xsl:variable>
                                 
                    <!-- lunghezza della sottostringa precedente all'elemento 
                         dato, considerando sia text che eventuali altri 
                         tref-tspan
                    -->
                    <xsl:variable name="len-prec">
                        <xsl:call-template name="calcola-lung-prec">
                            <xsl:with-param name="pos-end">
                                <xsl:value-of select="$pos" />
                            </xsl:with-param>
                            <xsl:with-param name="elemento">
                                <xsl:value-of select="$elemento" />
                            </xsl:with-param>
                        </xsl:call-template>
                    </xsl:variable>
                    
                    <!-- lunghezza della sottostringa che va da inizio testo
                         all'elemento tref-tspan precedente a quello 
                         considerato, più la lunghezza di quell'elemento
                         tref-tspan precedente.
                    -->
                    <xsl:variable name="len-prec-1">
                    
                        <xsl:variable name="len-text">
                            <xsl:call-template name="calcola-lung-prec">
                                <xsl:with-param name="pos-end">
                                    <xsl:value-of select="$pos - 1" />
                                </xsl:with-param>
                                <xsl:with-param name="elemento">
                                    <xsl:value-of select="$elemento-prec" />
                                </xsl:with-param>
                            </xsl:call-template>
                        </xsl:variable>
                        
                        <xsl:variable name="len-elemento">
                            <xsl:call-template name="calcola-lung-elemento">
                                <xsl:with-param name="pos">
                                    <xsl:value-of select="$pos - 1" />
                                </xsl:with-param>
                            </xsl:call-template>
                        </xsl:variable>
                        <xsl:value-of select="$len-text + $len-elemento" />
                    </xsl:variable>
                    
                    <!-- numero di caratteri della sottostringa precedente
                         all'elemento tref-tspan considerato 
                    -->
                    <xsl:variable name="char-prec">
                        <xsl:call-template name="calcola-lung-prec">
                            <xsl:with-param name="pos-end">
                                <xsl:value-of select="$pos" />
                            </xsl:with-param>
                            <xsl:with-param name="elemento">
                                <xsl:value-of select="$elemento" />
                            </xsl:with-param>
                            <xsl:with-param name="solo-caratteri">
                                <xsl:text>si</xsl:text>
                            </xsl:with-param>
                        </xsl:call-template>
                    </xsl:variable>
                    
                    <!-- numero di caratteri della sottostringa che va da
                         inizio testo fino all'elemento tref-tspan 
                         precedente a quello considerato più il numero
                         di caratteri di quell'elemento
                    -->
                    <xsl:variable name="char-prec-1">
                        <xsl:variable name="char-text">
                            <xsl:call-template name="calcola-lung-prec">
                                <xsl:with-param name="pos-end">
                                    <xsl:value-of select="$pos - 1" />
                                </xsl:with-param>
                                <xsl:with-param name="elemento">
                                    <xsl:value-of select="$elemento-prec" />
                                </xsl:with-param>
                                <xsl:with-param name="solo-caratteri">
                                    <xsl:text>si</xsl:text>
                                </xsl:with-param>
                            </xsl:call-template>
                        </xsl:variable>
                        <xsl:variable name="char-elemento">
                            <xsl:call-template name="calcola-lung-elemento">
                                <xsl:with-param name="pos">
                                    <xsl:value-of select="$pos - 1" />
                                </xsl:with-param>
                                <xsl:with-param name="solo-caratteri">
                                    <xsl:text>si</xsl:text>
                                </xsl:with-param>
                            </xsl:call-template>
                        </xsl:variable>
                        <xsl:value-of select="$char-text + $char-elemento" />
                    </xsl:variable>
                   
                   <!-- imposto il path, la cui lunghezza è data dai caratteri tra
                        l'elemento tref-tspan precedente e l'elemento
                        tref-tspan considerato 
                   -->
                    <xsl:variable name="move">
                        <xsl:text>m 0,0 </xsl:text>
                    </xsl:variable>
                                      
                    <xsl:variable name="line">
                        <xsl:text>l </xsl:text>
                        <xsl:value-of select="round($len-prec - $len-prec-1)" />
                        <xsl:text>,0 e</xsl:text>
                    </xsl:variable>
                           
                <!-- contiene il testo comprensivo di ogni tref-tspan -->
                <xsl:variable name="stringa-temp">
                    <xsl:variable name="testo-con-spazi">
                        <xsl:for-each select="..">
                                <xsl:apply-templates mode="spazio">
                                    <xsl:with-param name="carattere">
                                        <xsl:text>#</xsl:text>
                                    </xsl:with-param>
                                    <xsl:with-param name="pos">
                                        <xsl:text>-2</xsl:text>
                                    </xsl:with-param>
                                </xsl:apply-templates>
                        </xsl:for-each>      
                    </xsl:variable>
                    <xsl:call-template name="normalizza-spazi">
                        <xsl:with-param name="stringa">
                            <xsl:value-of select="$testo-con-spazi" />
                        </xsl:with-param>
                    </xsl:call-template>
                </xsl:variable>
                
                <!-- calcolo la porzione del testo che devo considerare -->
                <xsl:variable name="inizio">
                    <xsl:choose>
                        <xsl:when test="$pos = '1'">
                            <xsl:text>1</xsl:text>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:value-of select="$char-prec-1 + 1" />
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:variable>
                
                <xsl:variable name="how-much">
                    <xsl:choose>
                        <xsl:when test="$pos = '1'">
                            <xsl:value-of select="$char-prec" />
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:value-of select="$char-prec  - $char-prec-1" />
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:variable>

            <xsl:variable name="testo-temp">
                <xsl:value-of select="substring($stringa-temp,
                                      $inizio, $how-much)" />
            </xsl:variable>
       
            <xsl:variable name="path">    
                <xsl:value-of select="concat($move,$line)" />
            </xsl:variable>
            
            <!-- mi serve per assegnare un id univoco ad ogni porzione di
                 testo contenuta in text 
            -->
            <xsl:variable name="aggiustamento-id">
                <xsl:value-of select="count(preceding-sibling::svg:tspan) + 
                                      count(preceding-sibling::svg:tref)" />
            </xsl:variable>
    
            <!-- utilizzo il for-each per tornare nell'elemento che stavo
                 considerando, in modo da poter risalire ai suoi attributi
                 (font-size, ecc.), altrimenti mi troverei in tspan-tref
                 avendo attributi errati.
            -->
            <xsl:for-each select="parent::svg:text">
                <xsl:call-template name="svg-text-shape">
                    <xsl:with-param name="shift-x">
                        <!-- cerca eventuali elementi tref-tspan con 
                                attributo x, in modo da considerare
                                lo spostamento a partire da quel valore
                                e considerando la lunghezza del testo
                                successivo
                        -->
                        <xsl:call-template name="x-chunk">
                            <xsl:with-param name="n-tspan">
                                <xsl:value-of select="$pos - 1" />
                            </xsl:with-param>
                            <xsl:with-param name="base">
                                <xsl:value-of select="round($len-prec-1)" />
                            </xsl:with-param>
                        </xsl:call-template>
                    </xsl:with-param>
                    <!--
                    <xsl:with-param name="shift-y">
                        <xsl:value-of select="$shift-y" />
                    </xsl:with-param>
                    -->
                    <xsl:with-param name="id">
                        <xsl:value-of select="concat($shape-id-text,'-',
                                              $aggiustamento-id)" />
                    </xsl:with-param>
                    <xsl:with-param name="testo">
                        <xsl:value-of select="$testo-temp" />
                    </xsl:with-param>
                    <xsl:with-param name="path">
                        <xsl:value-of select="$path" />
                    </xsl:with-param>
                    <xsl:with-param name="n-tspan">
                        <xsl:value-of select="$pos - 1" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:for-each>
            
            
            <!-- se siamo nell'ultimo dobbiamo aggiungere i caratteri che restano dalla
                 fine di tspan in poi -->
            <xsl:if test="$pos = $num-elementi">
            
                <!-- testo dell'elemento tspan-tref corrente (l'ultimo) -->
                <xsl:variable name="last-t-testo">
                    <xsl:choose>
                        <xsl:when test="$elemento = 'tspan'">
                            <xsl:call-template name="normalizza-spazi">
                                <xsl:with-param name="stringa">
                                    <xsl:value-of select="." />
                                </xsl:with-param>
                            </xsl:call-template>                
                        </xsl:when>
                        <xsl:otherwise>
                             <xsl:variable name="nome-el">
                                <xsl:value-of select="substring(@xlink:href,2)" />
                             </xsl:variable>
                             <xsl:call-template name="normalizza-spazi">
                                <xsl:with-param name="stringa">
                                    <xsl:for-each select="//svg:defs/*[@id]">
                                        <xsl:if test="@id = $nome-el">
                                            <xsl:value-of select="." />
                                        </xsl:if>
                                    </xsl:for-each>
                                </xsl:with-param>
                            </xsl:call-template>
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:variable>

                <!-- lunghezza di quest'ultimo elemento -->
                <xsl:variable name="len-last-elemento">
                    <xsl:call-template name="calcola-lung-elemento">
                        <xsl:with-param name="pos">
                            <xsl:value-of select="$pos" />
                        </xsl:with-param>
                    </xsl:call-template>
                </xsl:variable>
            
                <xsl:variable name="last-tspan-length">                            
                    <xsl:value-of select="string-length($last-t-testo)" />
                </xsl:variable>
            
                <xsl:variable name="text-leng">
                    <xsl:value-of select="string-length($stringa-temp)" /> 
                </xsl:variable>
                
                <!-- ultima porzione di testo -->
                <xsl:variable name="testo-end">
                     <xsl:value-of select="substring($stringa-temp,
                                      $char-prec + $last-tspan-length + 1)" />
                </xsl:variable>
                
                <xsl:variable name="last-dim">
                     <xsl:value-of select="($font-s div $div-ff) + 
                                           ($font-s div $div-ff * $font-w )" />
                </xsl:variable>
                
                <xsl:variable name="m-end">
                    <xsl:text>m 0,0 </xsl:text>
                </xsl:variable>
                
                <xsl:variable name="l-end">
                     <xsl:text>l </xsl:text>
                     <xsl:value-of select="ceiling(string-length($testo-end) * 
                                                    $last-dim)" />
                     <xsl:text>,0 </xsl:text>
                </xsl:variable>
                
                
                <xsl:variable name="path-end">    
                    <xsl:value-of select="concat($m-end,$l-end)" />
                </xsl:variable>
                
                <!-- chiamo il template per gestire quest'ultima porzione di
                     testo. -->
                <xsl:for-each select="parent::svg:text">
                <xsl:call-template name="svg-text-shape">
                    <xsl:with-param name="shift-x">
                        <xsl:call-template name="x-chunk">
                            <xsl:with-param name="n-tspan">
                                <xsl:value-of select="$pos" />
                            </xsl:with-param>
                            <xsl:with-param name="base">
                                <xsl:value-of select="round($len-prec + 
                                                             $len-last-elemento)" />
                            </xsl:with-param>
                        </xsl:call-template>
                    </xsl:with-param>
                    <!--
                    <xsl:with-param name="shift-y">
                        <xsl:value-of select="$shift-y" />
                    </xsl:with-param>
                    -->
                    <xsl:with-param name="id">
                        <xsl:value-of select="concat($shape-id-text,'-END')" />
                    </xsl:with-param>
                    <xsl:with-param name="testo">
                        <xsl:value-of select="$testo-end" />
                    </xsl:with-param>
                    <xsl:with-param name="path">
                        <xsl:value-of select="$path-end" />
                    </xsl:with-param>
                    <xsl:with-param name="n-tspan">
                        <xsl:value-of select="$pos" />
                    </xsl:with-param>
                </xsl:call-template>
                </xsl:for-each>
                
            </xsl:if>   
            
            </xsl:for-each>
        </xsl:when>
        <!-- OOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOO -->
        <!-- OOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOO -->
        <!-- elemento text senza tspan e tref -->
        <!-- OOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOO -->
        <!-- OOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOO -->
        <xsl:otherwise>
            <!-- caso semplice -->
            
            <!-- trucchetto: aumento la dimensione del path e aggiungo degli spazi alla fine
                             in modo che per le conversioni inverse (vml-svg) il testo
                             non venga tagliato (tagli dovuti a piccoli aumenti della
                             dimensione del testo)
            -->
            <xsl:variable name="font-s-scalato">
                <xsl:call-template name="attributo-font-size" />
            </xsl:variable>

            <xsl:variable name="valore-text-anchor">
                <xsl:call-template name="valore-text-anchor" />
            </xsl:variable>

            <xsl:variable name="incremento">
                <xsl:choose>
                    <xsl:when test="$valore-text-anchor = 'middle'">
                        <xsl:text>4</xsl:text>
                    </xsl:when>
                    <xsl:when test="$valore-text-anchor = 'end'">
                        <xsl:text>5</xsl:text>
                    </xsl:when>
                    <xsl:when test="$valore-text-anchor = 'start'">
                        <xsl:text>4</xsl:text>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:text>4</xsl:text>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:variable>

            <xsl:variable name="new-testo">
                <xsl:choose>
                    <xsl:when test="$valore-text-anchor = 'middle'">
                        <xsl:value-of select="concat('  ',$testo,'  ')" />
                    </xsl:when>
                    <xsl:when test="$valore-text-anchor = 'end'">
                        <xsl:value-of select="concat('     ',$testo)" />
                    </xsl:when>
                    <xsl:when test="$valore-text-anchor = 'start'">
                        <xsl:value-of select="concat($testo,'    ')" />
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="concat($testo,'    ')" />
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:variable>
        
            <xsl:variable name="simple-path">
                <xsl:text>m 0,</xsl:text>
                <xsl:text>0</xsl:text>
                <xsl:text> l </xsl:text>
                
              <xsl:value-of select="ceiling((string-length($testo) + $incremento) 
                                             * $font-s-val)" /> 
                <xsl:text>,</xsl:text>
                <xsl:text>0</xsl:text>
                <xsl:text> e</xsl:text>
            </xsl:variable>
            <xsl:call-template name="svg-text-shape">
                    <xsl:with-param name="id">
                        <xsl:value-of select="$shape-id-text" />
                    </xsl:with-param>
                    <xsl:with-param name="testo">
                        <xsl:value-of select="$new-testo" />
                    </xsl:with-param>
                    <xsl:with-param name="path">
                        <xsl:value-of select="$simple-path" />
                    </xsl:with-param>
            </xsl:call-template>
        </xsl:otherwise>
        </xsl:choose>

        
        <!-- cerco all'interno di text altri elementi, non posso fare apply-template! -->
        <!-- problema: se faccio apply-templates mi butta fuori il contenuto di text, e 
             io non voglio che lo faccia, quindi devo ricorrere a questa soluzione. -->
        
        
        <xsl:for-each select="svg:tspan">
            <xsl:call-template name="svg-tspan">
                <xsl:with-param name="posizione">
                    <xsl:value-of select="position()" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:for-each>

        <xsl:for-each select="svg:tref">
            <xsl:call-template name="svg-tref" />
        </xsl:for-each>

        <xsl:for-each select="svg:textPath">
            <xsl:call-template name="svg-textPath" />
        </xsl:for-each>

        <!-- Non gestito -->
        <xsl:for-each select="svg:altGlyph">
            <xsl:call-template name="svg-altGlyph" />
        </xsl:for-each>    
        
    </xsl:when>
    
    <!-- OOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOO -->
    <!-- OOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOO -->    
    <!-- template chiamato da tpsan -->
    <!-- OOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOO -->
    <!-- OOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOO -->
    <xsl:otherwise>
        <xsl:variable name="path">
            <xsl:text>m 0,0 l </xsl:text>
            <xsl:value-of select="ceiling(( 
                        string-length($testo)) * (($font-s div $div-ff) + 
                                                 ($font-s div $div-ff * $font-w )))" />
            <xsl:text>,0 e</xsl:text>
        </xsl:variable>
        <xsl:call-template name="svg-text-shape">
            <xsl:with-param name="shift-x">
                <xsl:value-of select="$shift-x" />
            </xsl:with-param>
            <xsl:with-param name="shift-y">
                <xsl:value-of select="$shift-y" />
            </xsl:with-param>
            <xsl:with-param name="id">
                <xsl:value-of select="$shape-id-text" />
            </xsl:with-param>
            <xsl:with-param name="testo">
                <xsl:value-of select="$testo" />
            </xsl:with-param>
            <xsl:with-param name="path">
                <xsl:value-of select="$path" />
            </xsl:with-param>
        </xsl:call-template>
    </xsl:otherwise>
</xsl:choose>

</xsl:template>

<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->


<!-- NB: Questi elementi sono chiamati solo dentro text, altrimenti
     per ogni elemento che li può contenere bisogna fare la call-template.

-->
     

<!-- ********************************************************************************* --> 
<!-- ********************************************************************************* -->
<!-- ********************************* ELEMENTO TSPAN ******************************** -->
<!-- ********************************************************************************* -->
<!-- ********************************************************************************* -->
<xsl:template name="svg-tspan">
    <xsl:param name="posizione" />
    
        <xsl:variable name="pos">
            <xsl:value-of select="count(preceding-sibling::svg:tspan) + 
                                  count(preceding-sibling::svg:tref)  + 1" />
        </xsl:variable>
        
        <xsl:variable name="font-s">
            <xsl:call-template name="valore-font-size" />
        </xsl:variable>
        

        <!-- numero di caratteri che in text precedono tspan, comprende anche 
             eventuali altri tspan/tref -->
        <xsl:variable name="len-prec">
            <xsl:call-template name="calcola-lung-prec">
                <xsl:with-param name="pos-end">
                    <xsl:value-of select="$pos" />
                </xsl:with-param>
                <xsl:with-param name="elemento">
                    <xsl:text>tspan</xsl:text>
                </xsl:with-param>
            </xsl:call-template>
        </xsl:variable>

        <!-- chiamo il template di gestione della porzione di text:
                - specifico che sto gestendo un elemento tspan
                - imposto lo shify dovuto a porzioni di testo precedenti -->
        <xsl:call-template name="svg-text">
            <xsl:with-param name="tspan">
                <xsl:text>yes</xsl:text>
            </xsl:with-param>
            <xsl:with-param name="shift-x">
                <xsl:choose>
                    <!-- lo shift si fa solo se ne il tspan considerato o i suoi 
                         fratelli precedenti hanno l'attributo x. In questo caso ci si
                         sposta dalla posizione di x del padre di tanti spazi quanti
                         sono i caratteri della stringa del padre che precedono tspan
                    -->
                    <xsl:when test="@x">
                        <xsl:text>0</xsl:text>
                    </xsl:when>
                    <xsl:when test="preceding-sibling::svg:tref[@x] |
                                    preceding-sibling::svg:tspan[@x]|
                                    preceding-sibling::svg:textPath">
                                    
                            <!-- contiene la lunghezza del testo dall'inizio fino
                                 all'ultimo elemento (precedente al tspan considerato)
                                 che contiene l'attributo x -->
                            <xsl:variable name="len-prec-1">
                                <xsl:call-template name="c-prec" />
                            </xsl:variable>

                            <xsl:value-of select="$len-prec - $len-prec-1" />
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="$len-prec" />
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:with-param>
            <!-- shift-y: non è gestito, è lasciato per eventuali gestioni di testi
                          scritti in verticale. -->
            <!--<xsl:with-param name="shift-y">
                <xsl:call-template name="text-y" />
            </xsl:with-param>-->
        </xsl:call-template>
        
</xsl:template>

<!-- ********************************************************************************* --> 
<!-- ********************************************************************************* -->
<!-- ********************************* ELEMENTO TREF ********************************* -->
<!-- ********************************************************************************* -->
<!-- ********************************************************************************* -->
<xsl:template name="svg-tref">

    <xsl:variable name="nome-el">
        <xsl:value-of select="substring(@xlink:href,2)" />
    </xsl:variable>
    
    <xsl:variable name="string">
    <xsl:for-each select="//svg:defs/*[@id]">
        <xsl:if test="@id = $nome-el">
            <xsl:call-template name="normalizza-spazi">
                <xsl:with-param name="stringa">
                    <xsl:value-of select="." />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:if>    
    </xsl:for-each>
    </xsl:variable>

    <xsl:variable name="pos">
            <xsl:value-of select="count(preceding-sibling::svg:tspan) + 
                                  count(preceding-sibling::svg:tref)  + 1" />
    </xsl:variable>
        
    <xsl:variable name="font-s">
        <xsl:call-template name="valore-font-size" />
    </xsl:variable>
    
    <!-- numero di caratteri che in text precedono tref, comprende anche 
             eventuali altri tspan/tref -->
    <xsl:variable name="len-prec">
            <xsl:call-template name="calcola-lung-prec">
                <xsl:with-param name="pos-end">
                    <xsl:value-of select="$pos" />
                </xsl:with-param>
                <xsl:with-param name="elemento">
                    <xsl:text>tref</xsl:text>
                </xsl:with-param>
            </xsl:call-template>
    </xsl:variable>


    <xsl:call-template name="svg-text">
        <xsl:with-param name="shift-x">
            <xsl:choose>
                <xsl:when test="@x">
                    <xsl:text>0</xsl:text>
                </xsl:when>
                <xsl:when test="preceding-sibling::svg:tref[@x] |
                                preceding-sibling::svg:tspan[@x] |
                                preceding-sibling::svg:textPath">
                                
                    <!-- contiene la lunghezza del testo dall'inizio fino
                          all'ultimo elemento (precedente al tref considerato)
                          che contiene l'attributo x -->
                    <xsl:variable name="len-prec-1">
                        <xsl:call-template name="c-prec" />
                    </xsl:variable>

                    <xsl:value-of select="$len-prec - $len-prec-1 " />
                </xsl:when>
                <xsl:otherwise>
                        <xsl:value-of select="$len-prec" />
                </xsl:otherwise>
            </xsl:choose>
        </xsl:with-param>
        <xsl:with-param name="tref">
            <xsl:text>yes</xsl:text>
        </xsl:with-param>
        <xsl:with-param name="tref-string">
            <xsl:value-of select="$string" />
        </xsl:with-param>
    </xsl:call-template>

</xsl:template>

<!-- ********************************************************************************* --> 
<!-- ********************************************************************************* -->
<!-- ********************************* ELEMENTO TEXTPATH ***************************** -->
<!-- ********************************************************************************* -->
<!-- ********************************************************************************* -->
<xsl:template name="svg-textPath">

<xsl:variable name="nome-el">
    <xsl:value-of select="substring(@xlink:href,2)" />
</xsl:variable>
 
<xsl:variable name="path">
       <xsl:for-each select="//*[@id]">
        <xsl:if test="@id = $nome-el">
            <xsl:value-of select="@d" />
        </xsl:if>    
    </xsl:for-each>
</xsl:variable>

<!-- chiama la funzione di gestione del testo, passandogli il path, opportunamente
     modificato (cioè tradotto in sintassi vml)
-->
<xsl:call-template name="svg-text">
    <xsl:with-param name="tpath">
        <xsl:text>yes</xsl:text>
    </xsl:with-param>
    <xsl:with-param name="external-path">
        <xsl:call-template name="traduci-path">
            <xsl:with-param name="d">
                <xsl:value-of select="$path" />
            </xsl:with-param>
        </xsl:call-template>
    </xsl:with-param>
</xsl:call-template>
</xsl:template>

<!-- ********************************************************************************* --> 
<!-- ********************************************************************************* -->
<!-- ********************************* ELEMENTO ALTGLYPH ***************************** -->
<!-- ********************************************************************************* -->
<!-- ********************************************************************************* -->
<xsl:template name="svg-altGlyph">
    <!-- non gestito -->
    <!--<xsl:value-of select="." />-->
</xsl:template>


<!-- ******************************************************************************** -->
<!-- ***************** ELEMENTO: text  ********************************************** -->
<!-- ******************************************************************************** -->
<xsl:template match="svg:text">
    <xsl:choose>
    <xsl:when test="@transform">
        <xsl:variable name="x-val">     
           <xsl:call-template name="calcola-val-group-prec" >
                <xsl:with-param name="attributo"><xsl:text>x</xsl:text>
                </xsl:with-param>
           </xsl:call-template>
       </xsl:variable>
       
       <xsl:variable name="y-val">     
           <xsl:call-template name="calcola-val-group-prec" >
                <xsl:with-param name="attributo"><xsl:text>y</xsl:text>
                </xsl:with-param>
           </xsl:call-template>
       </xsl:variable>
       
       <xsl:variable name="w-val">     
           <xsl:call-template name="calcola-val-group-prec" />
       </xsl:variable>
       
       <xsl:variable name="h-val">     
           <xsl:call-template name="calcola-val-group-prec" >
                <xsl:with-param name="attributo"><xsl:text>h</xsl:text>
                </xsl:with-param>
           </xsl:call-template>
       </xsl:variable>
       
        <xsl:call-template name="transform-ric" >
            <xsl:with-param name="stringa">
                <xsl:value-of select="normalize-space(@transform)" />
            </xsl:with-param>
            <xsl:with-param name="w">
                <xsl:value-of select="$w-val" />
            </xsl:with-param>
            <xsl:with-param name="h">
                <xsl:value-of select="$h-val" />
            </xsl:with-param>
            <xsl:with-param name="x">
                <xsl:value-of select="$x-val" />
            </xsl:with-param>
            <xsl:with-param name="y">
                <xsl:value-of select="$y-val" />
            </xsl:with-param>
        </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
        <xsl:call-template name="svg-text" />
    </xsl:otherwise>
    </xsl:choose>
</xsl:template>

</xsl:stylesheet>
<!-- Stylus Studio meta-information - (c) 2004-2006. Progress Software Corporation. All rights reserved.
<metaInformation>
<scenarios ><scenario default="yes" name="Scenario1" userelativepaths="yes" externalpreview="no" url="..\XSL2" htmlbaseurl="" outputurl="" processortype="internal" useresolver="yes" profilemode="0" profiledepth="" profilelength="" urlprofilexml="" commandline="" additionalpath="" additionalclasspath="" postprocessortype="none" postprocesscommandline="" postprocessadditionalpath="" postprocessgeneratedext="" validateoutput="no" validator="internal" customvalidator=""/></scenarios><MapperMetaTag><MapperInfo srcSchemaPathIsRelative="yes" srcSchemaInterpretAsXML="no" destSchemaPath="" destSchemaRoot="" destSchemaPathIsRelative="yes" destSchemaInterpretAsXML="no"/><MapperBlockPosition></MapperBlockPosition><TemplateContext></TemplateContext><MapperFilter side="source"></MapperFilter></MapperMetaTag>
</metaInformation>
-->