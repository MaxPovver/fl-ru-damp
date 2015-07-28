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
        - svg-g 
        - svg-use
        
        - attributi-core
        - attributi-conditional
        - attributi-style
        - attributi-opacity
        - attributi-graphics
        - attributi-mask
        - attributi-graphical-event
        - attributi-document-event
        - attributi-cursor
        - attributi-external
        - attributi-clip
        - attributi-presentation
        - attributi-viewport
        - attributi-color-profile
        - attributi-xlink-embed
        - attributi-preserve-aspect-ratio
        
        - coord-origin-size
        - preceding-svg
        
    * match:
        - /
        - svg:svg
        - svg:desc
        - svg:title
        - svg:g
        - svg:style
        - svg:a
        - svg:use
        - svg:symbol
        - svg:defs
-->

<!-- NNNNBBBB: g non ammette x e y !!!!!!!!!!!!!!!!!!!!!!!! -->

<!-- problema con use: gli elementi richiamati non ereditano gli attributi --> 

<!-- ERRORE nella documentazione:
    1in = 90 pixel o 96 pixel ???
-->


<!--
Problema: abbiamo la nostra viewBox, se un'elemento va fuori dai margini non si vede,
            con vml si. (vedi Use01.svg - image01.svg): sembra ingestibile
-->


<!-- polyline, problema con i punti finali: risolto con trucchetto, ricongiungo con
        gli ultimi due punti -->

<!-- XXXXXXXXXXXXXXXX VARIABILI GLOBALI XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
    <xsl:variable name="n-elementi">
        <xsl:value-of select="count(//*)" />
    </xsl:variable>

<!-- Queste variabili rappresentano una dimensione "standard" in pixel della parte di
     schermo usabile per rappresentare l'immagine (escludendo la porzione occupata dalla
     barra dei browser), per schermi di 17 pollici, con risoluzione 800x600.
     Si usano come approssimazione quando un immagine non specifica la
     sua dimensioni oppure è espressa tramite percentuale.
-->
    <xsl:variable name="schermo-x">
        <xsl:text>750</xsl:text>
    </xsl:variable>

    <xsl:variable name="schermo-y">
        <xsl:text>400</xsl:text>
    </xsl:variable>

<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->

    <xsl:template match="/">
        <html>
            <head>
                <title>SVG2VMl</title>
                <style>v\:* {behavior:url(#default#VML);}</style>

                <xsl:apply-templates mode="head" />
            </head>
            <body>
                <xsl:apply-templates select="svg:svg" />
            </body>
        </html>
    </xsl:template>

<!-- ***********************************************************  -->
<!-- *****************************************************  -->
<!-- ******************************************* -->
<!-- ******************************** ELEMENTO SVG -->
<!-- ******************************************* -->
<!-- ************************************************************************* -->
<!-- ************************************************************************* -->
    <xsl:template match="svg:svg">

        <xsl:choose>
            <xsl:when test="((count(ancestor::svg:svg) = 0) and (@width | @height | @viewBox)) or
                (count(ancestor::svg:svg) &gt; 0)">

<!-- se l'elemento svg è il primo (e quindi non eredita attributi) e non ha nessun informazione
     sulle dimensioni (width, height o viewbox), elimino semplicemente l'elemento, in quanto non
     porta nessuna informazioni agli elementi figli.
     Negli altri casi viene gestito traducendolo con un elemento group.
-->    
                <v:group>

<!-- imposto i valori di style -->
                    <xsl:attribute name="style">
                        <xsl:text>position: absolute; </xsl:text>
                        <xsl:text>left: </xsl:text>
                        <xsl:choose>
                            <xsl:when test="@x">
                                <xsl:value-of select="@x" />
                            </xsl:when>
                            <xsl:otherwise>
                                <xsl:text>0</xsl:text>
                            </xsl:otherwise>
                        </xsl:choose>
                        <xsl:text>; </xsl:text>
                        <xsl:text>top: </xsl:text>
                        <xsl:choose>
                            <xsl:when test="@y">
                                <xsl:value-of select="@y" />
                            </xsl:when>
                            <xsl:otherwise>
                                <xsl:text>0</xsl:text>
                            </xsl:otherwise>
                        </xsl:choose>
                        <xsl:text>; </xsl:text>

                        <xsl:text>width: </xsl:text>
                        <xsl:choose>
    <!-- se ho degli elementi svg ancestor, e ho width, devo convertirlo in user unit -->
                            <xsl:when test="@width and (count(ancestor::svg:svg[@width | @height]) &gt; 0)">
                                <xsl:variable name="w-temp">
                                    <xsl:call-template name="conversione">
                                        <xsl:with-param name="attributo">
                                            <xsl:value-of select="@width" />
                                        </xsl:with-param>
                                        <xsl:with-param name="nome">
                                            <xsl:text>width</xsl:text>
                                        </xsl:with-param>
                                    </xsl:call-template>
                                </xsl:variable>
                                <xsl:value-of select="ceiling($w-temp)" />
                            </xsl:when>
    <!-- in questo caso, sono nel primo elemento svg, per cui width non dev'essere convertito in 
         user unit -->
                            <xsl:when test="@width">
                                <xsl:value-of select="@width" />
                            </xsl:when>
    <!-- se non ho width lo determino in base agli elementi svg precedenti -->
                            <xsl:when test="ancestor::svg:svg">
                                <xsl:call-template name="width-of-svg" />
                            </xsl:when>
                            <xsl:otherwise>
                                <xsl:text>100%</xsl:text>
                            </xsl:otherwise>
                        </xsl:choose>
                        <xsl:text>; </xsl:text>
                        <xsl:text>height: </xsl:text>
                        <xsl:choose>
                            <xsl:when test="@height and (count(ancestor::svg:svg[@width | @height]) &gt; 0)">
                                <xsl:variable name="h-temp">
                                    <xsl:call-template name="conversione">
                                        <xsl:with-param name="attributo">
                                            <xsl:value-of select="@height" />
                                        </xsl:with-param>
                                        <xsl:with-param name="nome">
                                            <xsl:text>height</xsl:text>
                                        </xsl:with-param>
                                    </xsl:call-template>
                                </xsl:variable>
                                <xsl:value-of select="ceiling($h-temp)" />
                            </xsl:when>
                            <xsl:when test="@height">
                                <xsl:value-of select="@height" />
                            </xsl:when>
                            <xsl:when test="ancestor::svg:svg">
                                <xsl:call-template name="height-of-svg" />
                            </xsl:when>
                            <xsl:otherwise>
                                <xsl:text>100%</xsl:text>
                            </xsl:otherwise>
                        </xsl:choose>
                        <xsl:text>; </xsl:text>
                        <xsl:text>z-index: -</xsl:text>
                        <xsl:value-of select="$n-elementi" />
                        <xsl:text>;</xsl:text>
                    </xsl:attribute>

<!-- imposto coordorigin e coordsize -->
                    <xsl:call-template name="coord-origin-size" />

                    <xsl:call-template name="attributi-preserve-aspect-ratio" />
                    <xsl:call-template name="attributi-style" />
                    <xsl:call-template name="attributi-core" />
                    <xsl:call-template name="attributi-conditional" />
                    <xsl:call-template name="attributi-graphical-event" />
                    <xsl:call-template name="attributi-external" />
                    <xsl:call-template name="attributi-presentation" />
                    <xsl:call-template name="attributi-document-event" />
                    <xsl:call-template name="attributo-title" />

                    <xsl:apply-templates />

                </v:group>
            </xsl:when>
            <xsl:otherwise>
<!-- non avendo ne width, ne height, ne viewbox, quest'elemento svg non porta 
     alcuna informazione, quindi lo elimino -->
                <xsl:apply-templates />
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

<!-- ********************************************************************************* -->
<!-- ********************************************************************************* -->
<!-- ************************ ELEMENTI DESC -  TITLE ********************************* -->
<!-- ********************************************************************************* -->
<!-- ********************************************************************************* -->
    <xsl:template match="svg:desc">
        <xsl:comment>
            <xsl:text>DESC: </xsl:text>
            <xsl:apply-templates />
        </xsl:comment>
    </xsl:template>

    <xsl:template match="svg:title">
        <xsl:comment>
            <xsl:text>TITLE: </xsl:text>
            <xsl:apply-templates />
        </xsl:comment>
    </xsl:template>

    <xsl:template name="attributo-title">
        <xsl:if test="svg:title">
            <xsl:for-each select="svg:title">
                <xsl:if test="position() = last()">
                    <xsl:attribute name="title">
                        <xsl:value-of select="." />
                    </xsl:attribute>
                </xsl:if>
            </xsl:for-each>
        </xsl:if>
    </xsl:template>

<!-- ********************************************************************************* --> 
<!-- ********************************************************************************* -->
<!-- ********************************* ELEMENTO G ************************************ -->
<!-- ********************************************************************************* -->
<!-- ********************************************************************************* -->
    <xsl:template name="svg-g">
        <xsl:param name="w">
            <xsl:text>-1</xsl:text>
        </xsl:param>
        <xsl:param name="h">
            <xsl:text>-1</xsl:text>
        </xsl:param>
        <xsl:param name="x">
            <xsl:text>-1</xsl:text>
        </xsl:param>
        <xsl:param name="y">
            <xsl:text>-1</xsl:text>
        </xsl:param>
        <xsl:param name="cs">
            <xsl:text>-1</xsl:text>
        </xsl:param>

<!-- crea il gruppo, impostando gli opportuni valori di dimensionamento, che possono
     essere passati in input, in base ad eventuali attributi di trasformazione -->

        <v:group>

            <xsl:call-template name="attributi-dimensione-group">
                <xsl:with-param name="w">
                    <xsl:value-of select="$w" />
                </xsl:with-param>
                <xsl:with-param name="h">
                    <xsl:value-of select="$h" />
                </xsl:with-param>
                <xsl:with-param name="x">
                    <xsl:value-of select="$x" />
                </xsl:with-param>
                <xsl:with-param name="y">
                    <xsl:value-of select="$y" />
                </xsl:with-param>
            </xsl:call-template>
   
            <xsl:call-template name="attributi-style" />
            <xsl:call-template name="attributi-core" />
            <xsl:call-template name="attributi-conditional" />
            <xsl:call-template name="attributi-graphical-event" />
            <xsl:call-template name="attributi-external" />
            <xsl:call-template name="attributi-presentation" />
            <xsl:call-template name="attributo-title" />
   <!--<xsl:call-template name="attributi-paint" />-->
   <!-- paint non mi interessa perchè i valori di stroke e fill vengono copiati nei figli.
        anche se ci fossero in group non verrebbero ereditati -->
   
            <xsl:if test="$cs != '-1'">
                <xsl:attribute name="coordsize">
                    <xsl:value-of select="$cs" />
                </xsl:attribute>
            </xsl:if>

            <xsl:apply-templates />
    
        </v:group>
    </xsl:template>

<!-- ********************************************************************************* -->
<!-- ************************** TEMPLATE per g *************************************** -->
    <xsl:template match="svg:g">

   <!-- contiene:
         -1 (non impostare coordsize, già impostato in gruppi precedenti)
         il valore di coordsize di default da inserire come attributo nel gruppo
   -->
        <xsl:variable name="cs">
            <xsl:call-template name="preceding-svg" />
        </xsl:variable>
   
        <xsl:choose>
    <!-- se ho transform, lo gestisco chiamando l'opportuno template, transform-ric, a
         cui devo passare 4 parametri, x,y,w,h (estratti da viewbox) dell'ultimo 
         elemento svg ancestor che servono per dimensionare i gruppi che vengono creati.
          
         Tuttavia, il gruppo corrente
         potrebbe essere contenuto in un'altro gruppo che contiene trasformazioni: se ha 
         trasformazioni quali rotate o translate non ci sono problemi, ma se ha scale ce ne
         sono, in quanto i valori di x,y,w,h, precedenti devono venire modificati in base
         al valore di scale e quindi per calcolari viene chiamato un opportuno template
         che calcola questa modifica (calcola-val-group-prec).
         Essendo VML sprovvisto di un attributo 'scale', per realizzarle si riutilizzano i 
         valori di width e height dell'ultimo elemento group ancestor e si imposta 
         l'attributo coordsize modificano (dividendolo od opportunamente aumentandolo) gli 
         attributi width e height ottenendo in questo modo una scale. 
         
         Quindi quando devo gestire un gruppo, devo impostare i valori di width, height 
         e coordsize; questi valori vengono impostati cercando negli attributi width, height 
         e viewbox di elementi ancestor.
         Supponiamo di avere un gruppo con scale (in svg), creerò il mio gruppo (in vml) con 
         un opportuno valore di coordsize. Dentro il mio gruppo (in svg) ho un'altro gruppo,
         lo traduco con un nuovo gruppo, senza trasformazioni o con traslazioni, in modo che
         gli attributi di dimensionamento debbano rimanere invariati e a cambiare siano solo
         i valori di x e y. Per impostare w,h o coordsize mi serve il coordsize 
         creato in precedenze, il quale non è possibile recuperarlo dal documento svg,
         perchè era rappresentato da una trasformazione di tipo scale e quindi chiamo una
         funzione che mi calcola il valore di scale di tutti i gruppi precedenti e 
         moltiplica il valore per l'attributo che gli passo in input (x,y,w,h).
         Invece se ho solo trasformazioni di tipo scale, non mi servono i valori di scale 
         precedenti perchè qui a contare è il rapporto tra w,h e coordsize (che rappresenta
         la scala).
         [...] 
    -->
    
    
            <xsl:when test="contains(@transform, 'scale') and
                    (contains(@transform,'translate') != '1')">
                    <!-- potrebbe servire anche rotate -->
   
                <xsl:call-template name="transform-ric" >
                    <xsl:with-param name="stringa">
                        <xsl:value-of select="normalize-space(@transform)" />
                    </xsl:with-param>
                    <xsl:with-param name="w">
                        <xsl:call-template name="width-of-svg" />
                    </xsl:with-param>
                    <xsl:with-param name="h">
                        <xsl:call-template name="height-of-svg" />
                    </xsl:with-param>
                    <xsl:with-param name="x">
                        <xsl:call-template name="x-of-svg" />
                    </xsl:with-param>
                    <xsl:with-param name="y">
                        <xsl:call-template name="y-of-svg" />
                    </xsl:with-param>
 
                </xsl:call-template>

            </xsl:when>
            <xsl:when test="@transform or ancestor::svg:g[@transform]">
                
                <xsl:variable name="x-val">
                    <xsl:call-template name="calcola-val-group-prec" >
                        <xsl:with-param name="attributo">
                            <xsl:text>x</xsl:text>

                        </xsl:with-param>
                    </xsl:call-template>
                </xsl:variable>
                <xsl:variable name="y-val">
                    <xsl:call-template name="calcola-val-group-prec" >
                        <xsl:with-param name="attributo">
                            <xsl:text>y</xsl:text>
                        </xsl:with-param>
                    </xsl:call-template>
                </xsl:variable>

                <xsl:variable name="w-val">
                    <xsl:call-template name="calcola-val-group-prec" />
                </xsl:variable>
       
                <xsl:variable name="h-val">
                    <xsl:call-template name="calcola-val-group-prec" >
                        <xsl:with-param name="attributo">
                            <xsl:text>h</xsl:text>
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
                    <xsl:with-param name="cs">
                        <xsl:value-of select="$cs" />
                    </xsl:with-param>
                </xsl:call-template>

            </xsl:when>

    <!-- se non ho transform chiamo il template per creare il gruppo -->
            <xsl:otherwise>
                <xsl:call-template name="svg-g" >
                    <xsl:with-param name="cs">
                        <xsl:value-of select="$cs" />
                    </xsl:with-param>
                </xsl:call-template>
        
            </xsl:otherwise>
        </xsl:choose>
   
    </xsl:template>

<!-- ********************************************************************************* --> 
<!-- ********************************************************************************* -->
<!-- ********************************* ELEMENTO STYLE ******************************** -->
<!-- ********************************************************************************* -->
<!-- ********************************************************************************* -->
    <xsl:template match="svg:style">
<!-- style viene gestito dagli elementi che ne fanno uso, proprierà di fill e stroke e
     altro. -->
    </xsl:template>

<!-- DA TOGLIERE -->
    <xsl:template match="*" mode="head">
<!-- cerca tutti gli elementi, se è presente style crea l'elemento style -->
        <xsl:for-each select="svg:style">
            <style>
            <!-- da cercare tutti gli attributi di style -->
                <xsl:attribute name="type">
                    <xsl:value-of select="@type" />
                </xsl:attribute>
     
                <xsl:value-of select="." />
            </style>
        </xsl:for-each>
    </xsl:template>

<!-- ********************************************************************************* -->
<!-- ********************************************************************************* -->
<!-- ********************************* ELEMENT SCRIPT ******************************** -->
<!-- ********************************************************************************* -->
<!-- ********************************************************************************* -->
    <xsl:template match="svg:script">
    </xsl:template>

    <xsl:template match="*" mode="head">
        <xsl:for-each select="svg:script">
            <script>
                <xsl:attribute name="language">
                    <xsl:text>javascript</xsl:text>
                </xsl:attribute>

                <xsl:value-of disable-output-escaping="yes" select="." />
            </script>
        </xsl:for-each>
    </xsl:template>


<!-- ********************************************************************************* --> 
<!-- ********************************************************************************* -->
<!-- ********************************* ELEMENTO A ************************************ -->
<!-- ********************************************************************************* -->
<!-- ********************************************************************************* -->
    <xsl:template match="svg:a">
<!-- non sono gestiti tutti gli attributi -->
        <a>
            <xsl:attribute name="href">
                <xsl:value-of select="@xlink:href" />
            </xsl:attribute>
            <xsl:apply-templates />
        </a>
    </xsl:template>

<!-- ********************************************************************************* -->
<!-- ********************************************************************************* -->
<!-- ************************ ELEMENTO USE ******************************************* -->
<!-- ********************************************************************************* -->
<!-- ********************************************************************************* -->
    <xsl:template name="svg-use">
        <xsl:param name="w">
            <xsl:text>-1</xsl:text>
        </xsl:param>
        <xsl:param name="h">
            <xsl:text>-1</xsl:text>
        </xsl:param>

        <xsl:variable name="cs">
            <xsl:call-template name="preceding-svg" />
        </xsl:variable>

<!-- NB: anche use definisce dei bordi al di fuori dei quali il contenuto non si vede, 
         clipping, non gestito da vml -->
        <v:group>

    
            <xsl:call-template name="attributi-dimensione-use">
                <xsl:with-param name="w">
                    <xsl:value-of select="$w" />
                </xsl:with-param>
                <xsl:with-param name="h">
                    <xsl:value-of select="$h" />
                </xsl:with-param>
            </xsl:call-template>

            <xsl:call-template name="attributi-core" />
            <xsl:call-template name="attributi-style" />
            <xsl:call-template name="attributi-presentation" />
            <xsl:call-template name="attributi-conditional" />
            <xsl:call-template name="attributi-graphical-event" />
    <!--<xsl:call-template name="attributi-xlink-embed" />-->
            <xsl:call-template name="attributi-external" />
            <xsl:call-template name="attributo-title" />

<!-- se c'è (e ci dev'essere) xlink:href, allora cerca l'elemento e mettilo qui -->

<!-- use si può riferire o a symbol o a defs (altri riferimenti non sono gestiti) -->

<!-- id dell'elemento senza # -->
            <xsl:variable name="nome-el">
                <xsl:value-of select="substring(@xlink:href,2)" />
            </xsl:variable>
    
    <!-- gestione di symbol -->
            <xsl:choose>
                <xsl:when test="//svg:symbol[@id = $nome-el]">
                    <xsl:for-each select="//svg:symbol[@id = $nome-el]">
                        <xsl:choose>
                            <xsl:when test="@viewBox">
                                <xsl:call-template name="coord-origin-size" />
                            </xsl:when>
                            <xsl:otherwise>
                                <xsl:for-each select="ancestor::svg:svg">
                                    <xsl:if test="position() = last()">
                                        <xsl:call-template name="coord-origin-size" />
                                    </xsl:if>
                                </xsl:for-each>
                            </xsl:otherwise>
                        </xsl:choose>
                    </xsl:for-each>
    
                    <xsl:call-template name="attributi-paint" />
        
                    <xsl:for-each select="//svg:symbol[@id = $nome-el]">
                        <xsl:apply-templates  />
                    </xsl:for-each>
                </xsl:when>
    
    <!-- se ho già trovato symbol non devo gestire nient'altro -->
                <xsl:otherwise>
                    <xsl:if test="$cs != '-1'">
                        <xsl:attribute name="coordsize">
                            <xsl:value-of select="$cs" />
                        </xsl:attribute>
                    </xsl:if>
    
        <!-- gestione di defs -->
                    <xsl:call-template name="attributi-paint" />
                    <xsl:for-each select="//*[@id = $nome-el]">

                        <xsl:apply-templates select="." />
                    </xsl:for-each>
                </xsl:otherwise>
            </xsl:choose>
    

        </v:group>
    </xsl:template>

<!-- ********************************************************************************* -->
<!-- ************************** TEMPLATE per use ************************************* -->
    <xsl:template match="svg:use">
   
        <xsl:variable name="cs">
            <xsl:call-template name="preceding-svg" />

        </xsl:variable>
   
   <!-- gestisco eventuali trasformazioni inserendo il contenuto di use in opportuni
        gruppi -->
        <xsl:choose>
            <xsl:when test="@transform">
                <xsl:call-template name="transform-ric" >
                    <xsl:with-param name="stringa">
                        <xsl:value-of select="normalize-space(@transform)" />
                    </xsl:with-param>
                    <xsl:with-param name="w">
                        <xsl:call-template name="width-of-svg" />
                    </xsl:with-param>
                    <xsl:with-param name="h">
                        <xsl:call-template name="height-of-svg" />
                    </xsl:with-param>
                    <xsl:with-param name="cs">
                        <xsl:value-of select="$cs" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
                <xsl:call-template name="svg-use" />
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

<!-- ********************************************************************************* -->
<!-- ********************************************************************************* -->
<!-- ************************ ELEMENTO SYMBOL **************************************** -->
<!-- ********************************************************************************* -->
<!-- ********************************************************************************* -->
    <xsl:template match="svg:symbol">
<!-- gestito all'interno di use -->
    </xsl:template>

<!-- ********************************************************************************* -->
<!-- ********************************************************************************* -->
<!-- ************************ ELEMENTO DEFS ****************************************** -->
<!-- ********************************************************************************* -->
<!-- ********************************************************************************* -->
    <xsl:template match="svg:defs">
<!-- gestito all'interno di use -->
    </xsl:template>


<!-- ******************************************************************************** -->
<!-- ******************************************************************************** -->
<!-- ******************************************************************************** -->
<!-- ******************************************************************************** -->
<!-- ************************** TEMPLATE ATTRIBUTI ********************************** -->
<!-- ******************************************************************************** -->
<!-- ******************************************************************************** -->
<!-- ******************************************************************************** -->
<!-- ******************************************************************************** -->


<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: attributi core ************************************* -->
<!-- ******************************************************************************** -->
    <xsl:template name="attributi-core">
<!-- ***** QUESTI SONO GLI ATTRIBUTI CORE **** -->
<!-- NON GESTITI: xml:base, xml:lang, xml:space -->
        <xsl:if test="@id">
            <xsl:attribute name="id">
                <xsl:value-of select="@id" />
            </xsl:attribute>
        </xsl:if>
<!-- ***************************************** -->
    </xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: attributi conditional ****************************** -->
<!-- ******************************************************************************** -->
    <xsl:template name="attributi-conditional">
<!-- ***** QUESTI SONO GLI ATTRIBUTI CONDITIONAL **** -->
<!-- NON GESTITI: requiredFeatures, requiredExtensions, systemLanguage -->
<!-- ************************************************ -->
    </xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: attributi style ************************************ -->
<!-- ******************************************************************************** -->
    <xsl:template name="attributi-style">
        <xsl:if test="@style">
    <!-- l'attributo style verrà gestito oppotunamente dai template di gestione di
            fill e stroke e di altre proprietà i cui valori possono essere contenuti
            in style -->
        </xsl:if>
        <xsl:if test="@class">
    <!-- class può essere usato per associare alcune proprietà definite nell'elemento
            style a determinati elementi. Queste associazioni sono gestite ad hoc, 
            durante la fase di gestione delle caratteristiche, quali fill e stroke,
            che possono avere valori definiti in style
    -->
            <xsl:attribute name="class">
                <xsl:value-of select="@class" />

            </xsl:attribute>
        </xsl:if>
    </xsl:template>


<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: attributi opacity ********************************** -->
<!-- ******************************************************************************** -->
    <xsl:template name="attributi-opacity">
<!-- ***** QUESTI SONO GLI ATTRIBUTI OPACITY **** -->
<!-- stroke-opacity, fill-opacity: gestiti dai template di gestione di fill e stroke -->

        <xsl:attribute name="opacity">
            <xsl:call-template name="opacity-ric" />
        </xsl:attribute>

    </xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: attributi graphics ********************************* -->
<!-- ******************************************************************************** -->
    <xsl:template name="attributi-graphics">
<!-- ***** QUESTI SONO GLI ATTRIBUTI GRAPHICS **** -->
<!-- NON GESTITI: display, image-rendering, pointer-events, shape-rendering,
text-rendering, visibility -->
<!-- ******************************************** -->
    </xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: attributi mask ************************************* -->
<!-- ******************************************************************************** -->
    <xsl:template name="attributi-mask">
<!-- ***** QUESTI SONO GLI ATTRIBUTI MASK ***** -->
<!-- NON GESTITI: mask -->
<!-- ******************************************** -->
    </xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: attributi graphical-event ************************** -->
<!-- ******************************************************************************** -->
    <xsl:template name="attributi-graphical-event">
<!-- ***** QUESTI SONO GLI ATTRIBUTI GRAPHICAL EVENT ***** -->
<!-- NON GESTITI: onfocusin, onfocusout, onactivate, onclick, onmousedown, onmouseup,
onmouseover, onmousemove, onmouseout, onload -->
<!-- ***************************************************** -->
    </xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: attributi document-event *************************** -->
<!-- ******************************************************************************** -->
    <xsl:template name="attributi-document-event">
<!-- ***** QUESTI SONO GLI ATTRIBUTI DOCUMENT EVENT ***** -->
<!-- NON GESTITI: onunload, onabort, onerror, onresize, onscroll, onzoom -->
<!-- ***************************************************** -->
    </xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: attributi cursor *********************************** -->
<!-- ******************************************************************************** -->
    <xsl:template name="attributi-cursor">
<!-- ***** QUESTI SONO GLI ATTRIBUTI CURSOR ***** -->
<!-- NON GESTITI: cursor -->
<!-- ******************************************** -->
    </xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: attributi external ********************************* -->
<!-- ******************************************************************************** -->
    <xsl:template name="attributi-external">
<!-- ***** QUESTI SONO GLI ATTRIBUTI EXTERNAL ***** -->
<!-- NON GESTITI: externalResourcesRequired -->
<!-- ********************************************** -->
    </xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: attributi clip ************************************* -->
<!-- ******************************************************************************** -->
    <xsl:template name="attributi-clip">
<!-- ***** QUESTI SONO GLI ATTRIBUTI CLIP ********* -->
<!-- NON GESTITI: clip-path, clip-rule -->
<!-- ********************************************** -->
    </xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: attributi presentation ***************************** -->
<!-- ******************************************************************************** -->
    <xsl:template name="attributi-presentation">
<!-- ***** QUESTI SONO GLI ATTRIBUTI PRESENTATION ********* -->
<!-- non gestiti -->
<!-- ****************************************************** -->
    </xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: attributi viewport ********************************* -->
<!-- ******************************************************************************** -->
    <xsl:template name="attributi-viewport">
<!-- ***** QUESTI SONO GLI ATTRIBUTI VIEWPORT ********* -->
<!-- NON GESTITI: clip, overflow -->
<!-- ****************************************************** -->
    </xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: attributi color profile **************************** -->
<!-- ******************************************************************************** -->
    <xsl:template name="attributi-color-profile">
<!-- ***** QUESTI SONO GLI ATTRIBUTI COLOR PROFILE ********* -->
<!-- non gestiti -->
<!-- ******************************************************* -->
    </xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: attributi xlink embed ****************************** -->
<!-- ******************************************************************************** -->
    <xsl:template name="attributi-xlink-embed">
<!-- ***** QUESTI SONO GLI ATTRIBUTI XLINK EMBED ********* -->
<!--  NON GESTITI: xlink:type, xlink:role, xlink:arcrole, xlink:title, 
xlink:show, xlink:actuate -->

        <xsl:if test="@xlink:href">
            <xsl:attribute name="type">
                <xsl:value-of select="@xlink:href" />
            </xsl:attribute>
        </xsl:if>

    </xsl:template>


<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: attributi peserve aspect ratio ********************* -->
<!-- ******************************************************************************** -->
    <xsl:template name="attributi-preserve-aspect-ratio">

<!-- non gestito, forse neanche gestibile -->
    
    
<!--<xsl:choose>-->
<!--<xsl:when test="@preserveAspectRatio">-->
        <!-- valori:
                - defer
                - align
                - meetOrSlice
        -->

<!--<xsl:choose>-->
<!--<xsl:when test="name() = 'svg' or name() = 'symbol' or name() = 'foreignObject' or -->
<!--                name() = 'marker' or name() = 'pattern' or name() = 'view'">-->
        <!-- se c'è viewBox, allora considera quest'attributo altrimenti no! -->
<!--<xsl:if test="@viewBox">-->
        <!-- analizziamo il valore di align -->
     <!--<xsl:choose>-->
         <!--<xsl:when test="contains(@preserveAspectRatio, 'none')"> -->
            <!-- non devo fare niente, è il comportamento
                 di default di vml -->
        <!--</xsl:when>-->
        <!--<xsl:when test="contains(@preserveAspectRatio, 'xMinYMin')">-->
        <!--</xsl:when>-->
    <!--</xsl:choose>-->
<!--</xsl:if>-->
<!--</xsl:when>-->
<!--<xsl:when test="name() = 'image'">-->
    <!-- qui la gestione è un po' differente -->
<!--</xsl:when>-->
<!--<xsl:otherwise>-->
<!--</xsl:otherwise>-->
<!--</xsl:choose>-->
<!--</xsl:when>-->

    <!-- se non c'è questo attributo bisogna moficare l'aspetto ugualmente perchè i valori
         di default di svg e vml non corrispondono -->
<!--<xsl:otherwise>-->
<!--</xsl:otherwise>-->
<!--</xsl:choose>-->

    </xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: coordorigin - coordsize **************************** -->
<!-- ******************************************************************************** -->
    <xsl:template name="coord-origin-size">

<!-- imposto coordorign e coordsize -->
        <xsl:choose>
            <xsl:when test="@viewBox">

        <!-- ********************************************************************* -->
        <!-- variabili con i valori di viewBox -->
                <xsl:variable name="val-left"> <!-- valore della x -->
                    <xsl:value-of select="substring-before(normalize-space(@viewBox),' ')" />
                </xsl:variable>
                <xsl:variable name="val-top"> <!-- valore della y -->
                    <xsl:value-of select="substring-before(substring-after
                            (normalize-space(@viewBox),' '),' ')" />
                </xsl:variable>
        
                <xsl:variable name="val-width">
                    <xsl:value-of select="substring-before(substring-after
        (substring-after(normalize-space(@viewBox),' '),' '),' ')" />
                </xsl:variable>
        
                <xsl:variable name="val-height">
                    <xsl:value-of select="substring-after(substring-after
        (substring-after(normalize-space(@viewBox),' '),' '),' ')" />
                </xsl:variable>
        
        <!-- *********************************************************************** -->
        
                <xsl:attribute name="coordorigin">
        <!-- primi due valori di viewBox -->
                    <xsl:value-of select="concat($val-left,' ',$val-top)" />
                </xsl:attribute>
        
                <xsl:attribute name="coordsize">
        <!-- ultimi due valori di viewBox -->
                    <xsl:value-of select="concat($val-width,' ',$val-height)" />
                </xsl:attribute>
            </xsl:when>
    
    <!-- non abbiamo viewBox:
        è come se ci fosse viewBox con gli ultimi due valori impostati
        con width e height, convertiti in pixel
        CONVERSIONI:
            1. 1pt = 1.25px
            2. 1pc = 15px
            3. 1mm = 3.543307px
            4. 1cm = 35.43307px
            5. 1in = 90px o 96px ???? Per adesso mettiamo 96, discordanze nella documentazione
            6. em: font-size corrente
            7. ex: x-height
    -->
            <xsl:otherwise>
                <xsl:attribute name="coordorigin">
                    <xsl:text>0 0</xsl:text>
                </xsl:attribute>
        
        <!-- impostiamo coordsize -->

                <xsl:variable name="w-temp">
                    <xsl:variable name="w-effettivo">
                        <xsl:call-template name="svg-width" />
                    </xsl:variable>
                    <xsl:choose>
                        <xsl:when test="contains($w-effettivo,'%')">
                            <xsl:value-of select="(normalize-space(
                                           substring-before($w-effettivo,'%')) * $schermo-x)
                                           div 100" />
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:value-of select="$w-effettivo" />
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:variable>
        
                <xsl:variable name="h-temp">
                    <xsl:variable name="h-effettivo">
                        <xsl:call-template name="svg-height" />
                    </xsl:variable>
                    <xsl:choose>
                        <xsl:when test="contains($h-effettivo,'%')">
                            <xsl:value-of select="(normalize-space(
                                           substring-before($h-effettivo,'%')) * $schermo-y) 
                                           div 100" />
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:value-of select="$h-effettivo" />
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:variable>

      
                <xsl:attribute name="coordsize">
                    <!-- primo valore di coordsize -->
                    <!-- valore di width -->
                    <xsl:value-of select="$w-temp" />
                    <xsl:text> </xsl:text>
                    <!-- secondo valore di coordsize -->
                    <!-- valore di height -->
                    <xsl:value-of select="$h-temp" />
                </xsl:attribute>
        
            </xsl:otherwise>
        </xsl:choose>

    </xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: preceding-svg *************************************** -->
<!-- ******************************************************************************** -->
    <xsl:template name="preceding-svg">
<!-- controllo per inserimento coordsize:
        - se c'è un svg precedente con w,h,vb: non fare niente
        - se non c'è: inserisci coordsize: SCHERMO-X,SCHERMO-Y -->
        
        <xsl:choose>
            <xsl:when test="ancestor::svg:svg[@viewBox] or ancestor::svg:svg[@width] or
                        ancestor::svg:svg[@height] or ancestor::g">
                <xsl:text>-1</xsl:text>
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="concat($schermo-x,' ',$schermo-y)" />
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

<!-- ****************************************************************************** -->
<!-- ****************************************************************************** -->
<!-- ****************************************************************************** -->
<!-- ****************************************************************************** -->
<!-- ****************************************************************************** -->
<!-- E qui siamo giunti alla fine, includiamo gli altri file e chiudiamo il tutto!! -->
<!-- ****************************************************************************** -->
<!-- ****************************************************************************** -->
<!-- ****************************************************************************** -->
<!-- ****************************************************************************** -->
<!-- ****************************************************************************** -->

    <xsl:include href="../classes/XSL2/rect.xsl" />
    <xsl:include href="../classes/XSL2/dimensioni.xsl" />
    <xsl:include href="../classes/XSL2/transform.xsl" />
    <xsl:include href="../classes/XSL2/xywh.xsl" />
    <xsl:include href="../classes/XSL2/predef.xsl" />
    <xsl:include href="../classes/XSL2/attributi-paint.xsl" />
    <xsl:include href="../classes/XSL2/text.xsl" />
    <xsl:include href="../classes/XSL2/text-dim.xsl" />
    <xsl:include href="../classes/XSL2/text-elemento.xsl" />
    <xsl:include href="../classes/XSL2/text-ric.xsl" />
    <xsl:include href="../classes/XSL2/gradient-pattern.xsl" />
    <xsl:include href="../classes/XSL2/marker.xsl" />
    <xsl:include href="../classes/XSL2/filter.xsl" />
    <xsl:include href="../classes/XSL2/gestione-style.xsl" />
    <xsl:include href="../classes/XSL2/path.xsl" />
    <xsl:include href="../classes/XSL2/matematica.xsl" />

</xsl:stylesheet>
