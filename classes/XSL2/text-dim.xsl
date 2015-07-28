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
        - attributi-text
        - attributi-text-content
            - svg-text-anchor
        - attributi-font

        - valore-font-family
        - valore-font-size
        - attributo-font-size (font-size piu' aggiustamenti)
        - valore-font-weight
        - valore-text-decoration
        - valore-text-anchor
        - valore-font-variant
        - valore-font-style

        - font-w-perc
        - divisione-font-family

        - sostituzione
        
    * match:
        - svg:tspan     mode=spazio
        - svg:tref      mode=tref-spazio
        - svg:tref      mode=spazio
        - svg:textPath  mode=spazio
        - svg:textPath  mode=tref-spazio
        - svg:textPath  mode=vuoto
-->


<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: attributi text ************************************* -->
<!-- ******************************************************************************** -->
<xsl:template name="attributi-text">
<!-- ***** QUESTI SONO GLI ATTRIBUTI TEXT ******* -->
<!-- Non gestiti: writing-mode -->
<!-- ******************************************** -->
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: attributi textContent ****************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="attributi-text-content">
<!-- ***** QUESTI SONO GLI ATTRIBUTI TEXT CONTENT **** -->
<!-- Non gestiti: alignment-baseline, baseline-shift, direction, dominant-baseline, 
glyph-orientation-horizontal, glyph-orientation-vertical, kerning, letter-spacing, 
unicode-bidi, word-spacing -->
<!-- ************************************************* -->

<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXX text-anchor XXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<!-- gestito nel calcolo del valore di x, con il template svg-text-anchor -->

<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXX text-decoration XXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<!-- Non funziona correttamente!!! -->

<xsl:variable name="decoration">
    <xsl:call-template name="valore-text-decoration" />
</xsl:variable>

<xsl:if test="normalize-space($decoration) != 'none'">
    <xsl:text>text-decoration: </xsl:text>
        <xsl:value-of select="$decoration" />
    <xsl:text>; </xsl:text>
</xsl:if>


</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: svg text anchor ************************************ -->
<!-- ******************************************************************************** -->
<xsl:template name="svg-text-anchor">
<!-- restituisce il valore di text anchor, eventualmente ereditato dagli elementi
     precedenti. Se non c'è restituisce start
-->

<xsl:call-template name="valore-text-anchor" />

</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: attributi font ************************************* -->
<!-- ******************************************************************************** -->
<xsl:template name="attributi-font">

<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXX font-style XXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->

<xsl:variable name="font-style">
    <xsl:call-template name="valore-font-style" />
</xsl:variable>
<xsl:if test="normalize-space($font-style) != 'normal'">
    <xsl:text>font-style: </xsl:text>
        <xsl:value-of select="$font-style" />
    <xsl:text>; </xsl:text>
</xsl:if>

<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXX font-weight XXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<xsl:variable name="font-w-temp">
    <xsl:call-template name="valore-font-weight" />
</xsl:variable>

<xsl:if test="normalize-space($font-w-temp) != 'normal'">
        <xsl:text>font-weight: </xsl:text>
            <xsl:value-of select="$font-w-temp" />
        <xsl:text>; </xsl:text>  
</xsl:if>

<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXX font-variant XXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->

<xsl:variable name="font-v">
    <xsl:call-template name="valore-font-variant" />
</xsl:variable>

<xsl:if test="normalize-space($font-v) != 'normal'">
    <xsl:text>font-variant: </xsl:text>
        <xsl:value-of select="$font-v" />
    <xsl:text>; </xsl:text>
</xsl:if>

<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXX font-stretch XXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<!-- non è supportato da textpath!!! -->
<!--
<xsl:choose>
    <xsl:when test="@font-stretch">
        <xsl:text>font-variant: </xsl:text>
            <xsl:value-of select="@font-stretch" />
        <xsl:text>; </xsl:text>  
    </xsl:when>
    <xsl:when test="count(ancestor::*[@font-stretch]) > 0">
        <xsl:for-each select="ancestor::*[@font-stretch]">
            <xsl:if test="position() = last()">
                <xsl:text>font-stretch: </xsl:text>
                    <xsl:value-of select="@font-stretch" />
                <xsl:text>; </xsl:text>
            </xsl:if>
        </xsl:for-each>
    </xsl:when>
    <xsl:otherwise>-->
        <!--<xsl:text>font-stretch: normal; </xsl:text>--><!--
    </xsl:otherwise>
</xsl:choose>
-->

<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXX font-size-adjust XXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<!-- non è supportato da textpath!!! -->
<!--
<xsl:choose>
    <xsl:when test="@font-size-adjust">
        <xsl:text>font-size-adjust: </xsl:text>
            <xsl:value-of select="@font-size-adjust" />
        <xsl:text>; </xsl:text>  
    </xsl:when>
    <xsl:when test="count(ancestor::*[@font-size-adjust]) > 0">
        <xsl:for-each select="ancestor::*[@font-size-adjust]">
            <xsl:if test="position() = last()">
                <xsl:text>font-size-adjust: </xsl:text>
                    <xsl:value-of select="@font-size-adjust" />
                <xsl:text>; </xsl:text>
            </xsl:if>
        </xsl:for-each>
    </xsl:when>
    <xsl:otherwise>-->
        <!--<xsl:text>font-size-adjust: none; </xsl:text>--><!--
    </xsl:otherwise>
</xsl:choose>
-->

<!-- NB: questo template viene chiamato all'interno dell'attributo style!! 
         Non lo scordare. -->

<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXX font-family XXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<xsl:variable name="font-f-temp">
    <xsl:call-template name="valore-font-family" />
</xsl:variable>

<xsl:text>font-family: </xsl:text>
    <xsl:value-of select="$font-f-temp" />
<xsl:text>; </xsl:text>

<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXX font-size XXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<xsl:text>font-size: </xsl:text>
    <xsl:variable name="font-s">
        <xsl:call-template name="attributo-font-size" />
    </xsl:variable>
    <xsl:value-of select="ceiling(($font-s * 10) div 10)" />
            <xsl:text>px</xsl:text>
<xsl:text>; </xsl:text>                        
    
</xsl:template>




<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: valore font-family ********************************* -->
<!-- ******************************************************************************** -->
<xsl:template name="valore-font-family">
<!-- cerco il valore di font-family, se non lo trovo nell'elemento selezionato lo cerco
    negli elementi ancestor -->

   <!-- serve per scegliere quale style applicare, se quello associato all'id, a class o 
     all'elemento, oppure nessuno dei 3 -->
    <xsl:variable name="style-type">
        <xsl:call-template name="var-style-type">
            <xsl:with-param name="attributo">
                <xsl:text>font-family</xsl:text>
            </xsl:with-param>
        </xsl:call-template>
    </xsl:variable>

<xsl:choose>
<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<!-- XXXXXXXXXXXXXXXXXXXXXXX GESTIONE STYLE XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<xsl:when test="(@style and (contains(@style, 'font-family ') or 
                contains(@style, 'font-family:'))) or
                (@id and substring($style-type,1,1) = 'i') or
                (@class and substring($style-type,1,1) = 'c') or
                (substring($style-type,1,1) = 'e')">
    <xsl:variable name="font-f-temp">
        <xsl:call-template name="ricerca-style">
            <xsl:with-param name="attributo">
                <xsl:text>font-family</xsl:text>
            </xsl:with-param>
            <xsl:with-param name="style-type">
                <xsl:value-of select="$style-type" />
            </xsl:with-param>
        </xsl:call-template>
    </xsl:variable>
    
    <xsl:value-of select="$font-f-temp" />
</xsl:when>
<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<!-- XXXXXXXXXXXXXXXXXXXXXXX ATTRIBUTO FONT-FAMILY XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<xsl:when test="@font-family">
      <xsl:variable name="font-f-temp">
        <xsl:value-of select="@font-family" />
      </xsl:variable>
                                          
        <xsl:value-of select="$font-f-temp" />
</xsl:when>
<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<!-- XXXXXXXXXXXXXXXXXXXXXXX RICORSIONE XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<xsl:when test="count(ancestor::svg:*) > 0">
        <xsl:for-each select="ancestor::svg:*">
        <xsl:if test="position() = last()">
            <xsl:call-template name="valore-font-family" />
        </xsl:if>
        </xsl:for-each>
</xsl:when>
<xsl:otherwise>
    <!-- a questo punto non abbiamo trovato font-family in nessun elemento, 
        dobbiamo metterci un valore di default, Arial -->
    <xsl:text>Arial</xsl:text>
</xsl:otherwise>
</xsl:choose>

</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: valore font-size *********************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="valore-font-size">
<!-- cerco il valore di font-size, se non lo trovo nell'elemento selezionato lo cerco
    negli elementi ancestor -->
    
    <!-- seve per scegliere quale style applicare, se quello associato all'id, a class o 
     all'elemento, oppure nessuno dei 3 -->
    <xsl:variable name="style-type">
        <xsl:call-template name="var-style-type">
            <xsl:with-param name="attributo">
                <xsl:text>font-size</xsl:text>
            </xsl:with-param>
        </xsl:call-template>
    </xsl:variable>

<xsl:choose>
<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<!-- XXXXXXXXXXXXXXXXXXXXXXX GESTIONE STYLE XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<xsl:when test="(@style and (contains(@style, 'font-size ') or 
                contains(@style, 'font-size:'))) or
                (@id and substring($style-type,1,1) = 'i') or
                (@class and substring($style-type,1,1) = 'c') or
                (substring($style-type,1,1) = 'e')">
    <xsl:variable name="font-s-temp">
        <xsl:call-template name="ricerca-style">
            <xsl:with-param name="attributo">
                <xsl:text>font-size</xsl:text>
            </xsl:with-param>
            <xsl:with-param name="style-type">
                <xsl:value-of select="$style-type" />
            </xsl:with-param>
        </xsl:call-template>
    </xsl:variable>
    
    <xsl:call-template name="conversione" >
        <xsl:with-param name="attributo">
            <xsl:value-of select="$font-s-temp" />
        </xsl:with-param>
        <xsl:with-param name="nome">
            <xsl:text>font-size</xsl:text>
        </xsl:with-param>
    </xsl:call-template>
    <!--<xsl:value-of select="$font-s-temp" />-->
    
</xsl:when>
<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<!-- XXXXXXXXXXXXXXXXXXXXXXX ATTRIBUTO FONT-SIZE XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<xsl:when test="@font-size">
      <xsl:variable name="font-s-temp">
            <xsl:call-template name="conversione" >
                <xsl:with-param name="attributo">
                    <xsl:value-of select="@font-size" />
                </xsl:with-param>
                <xsl:with-param name="nome">
                    <xsl:text>font-size</xsl:text>
                </xsl:with-param>
            </xsl:call-template>
        </xsl:variable>
                                          
        <xsl:value-of select="$font-s-temp" />
</xsl:when>
<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<!-- XXXXXXXXXXXXXXXXXXXXXXX RICORSIONE XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<xsl:when test="count(ancestor::svg:*) > 0">
        <xsl:for-each select="ancestor::svg:*">
        <xsl:if test="position() = last()">
            <xsl:call-template name="valore-font-size" />
        </xsl:if>
        </xsl:for-each>
</xsl:when>

<xsl:otherwise>
    <!-- a questo punto non abbiamo trovato font-size in nessun elemento, 
        dobbiamo metterci un valore di default, 12 -->
    <xsl:text>12</xsl:text>
</xsl:otherwise>
</xsl:choose>
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: attributo font-size ******************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="attributo-font-size">
    <xsl:param name="scale-val"><xsl:text>-1</xsl:text></xsl:param>
    
    <!-- calcola e resituisce il valore di font-size (opportunatamente aggiustato) -->
    <!-- per maggiori dettagli su divisione e scale-value vedi template stroke-width -->
    
    <xsl:variable name="divisione">
        <xsl:call-template name="divisione-vb">
            <xsl:with-param name="wh">
                <xsl:text>no</xsl:text>
            </xsl:with-param>
        </xsl:call-template>
    </xsl:variable>
    
    <xsl:variable name="scale-value">
        <xsl:choose>
            <xsl:when test="$scale-val = '-1'">
                <xsl:call-template name="calcola-scala" />
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="$scale-val" />
            </xsl:otherwise>
        </xsl:choose>
    </xsl:variable>
    
    <!-- seve per scegliere quale style applicare, se quello associato all'id, a class o 
     all'elemento, oppure nessuno dei 3 -->
    <xsl:variable name="style-type">
        <xsl:call-template name="var-style-type">
            <xsl:with-param name="attributo">
                <xsl:text>font-size</xsl:text>
            </xsl:with-param>
        </xsl:call-template>
    </xsl:variable>
    
<xsl:choose>
<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<!-- XXXXXXXXXXXXXXXXXXXXXXX GESTIONE STYLE XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<xsl:when test="(@style and (contains(@style, 'font-size ') or 
                contains(@style, 'font-size:'))) or
                (@id and substring($style-type,1,1) = 'i') or
                (@class and substring($style-type,1,1) = 'c') or
                (substring($style-type,1,1) = 'e')">
    <xsl:variable name="font-s-temp">
        <xsl:call-template name="ricerca-style">
            <xsl:with-param name="attributo">
                <xsl:text>font-size</xsl:text>
            </xsl:with-param>
            <xsl:with-param name="style-type">
                <xsl:value-of select="$style-type" />
            </xsl:with-param>
        </xsl:call-template>
    </xsl:variable>

    <xsl:variable name="font-s-conv">
    <xsl:call-template name="conversione" >
        <xsl:with-param name="attributo">
            <xsl:value-of select="$font-s-temp" />
        </xsl:with-param>
        <xsl:with-param name="nome">
            <xsl:text>font-size</xsl:text>
        </xsl:with-param>
    </xsl:call-template>
        </xsl:variable>

    <xsl:value-of select="($font-s-conv div $divisione) * $scale-value" />

    
</xsl:when>
<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<!-- XXXXXXXXXXXXXXXXXXXXXXX ATTRIBUTO FONT-SIZE XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<xsl:when test="@font-size">
        <xsl:variable name="font-s-temp">
            <xsl:call-template name="conversione" >
                <xsl:with-param name="attributo">
                    <xsl:value-of select="@font-size" />
                </xsl:with-param>
                <xsl:with-param name="nome">
                    <xsl:text>font-size</xsl:text>
                </xsl:with-param>
            </xsl:call-template>
        </xsl:variable>
                                          

        <xsl:value-of select="($font-s-temp div $divisione) * $scale-value" />

</xsl:when>
<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<!-- XXXXXXXXXXXXXXXXXXXXXXX RICORSIONE XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<xsl:otherwise>
        <xsl:choose>
            <xsl:when test="name() = 'svg'">
                <xsl:choose>
                   <xsl:when test="@font-size">
                        <!-- caso gestito in precedenza -->
                    </xsl:when>
                    <xsl:otherwise>
                            <xsl:value-of select="$scale-value * 12" />
                    </xsl:otherwise>        
                </xsl:choose>
            </xsl:when>
            <xsl:otherwise>
                    <xsl:for-each select="..">
                        <xsl:call-template name="attributo-font-size">
                            <xsl:with-param name="scale-val">
                                <xsl:value-of select="$scale-value" />
                            </xsl:with-param>
                        </xsl:call-template>
                    </xsl:for-each>
            </xsl:otherwise>
        </xsl:choose>
</xsl:otherwise>
</xsl:choose>
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: valore font-weight ********************************* -->
<!-- ******************************************************************************** -->
<xsl:template name="valore-font-weight">
<!-- cerco il valore di font-weight, se non lo trovo nell'elemento selezionato lo cerco
    negli elementi ancestor -->

   <!-- serve per scegliere quale style applicare, se quello associato all'id, a class o 
     all'elemento, oppure nessuno dei 3 -->
    <xsl:variable name="style-type">
        <xsl:call-template name="var-style-type">
            <xsl:with-param name="attributo">
                <xsl:text>font-weight</xsl:text>
            </xsl:with-param>
        </xsl:call-template>
    </xsl:variable>

<xsl:choose>
<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<!-- XXXXXXXXXXXXXXXXXXXXXXX GESTIONE STYLE XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<xsl:when test="(@style and (contains(@style, 'font-weight ') or 
                contains(@style, 'font-weight:'))) or
                (@id and substring($style-type,1,1) = 'i') or
                (@class and substring($style-type,1,1) = 'c') or
                (substring($style-type,1,1) = 'e')">
    <xsl:variable name="font-w-temp">
        <xsl:call-template name="ricerca-style">
            <xsl:with-param name="attributo">
                <xsl:text>font-weight</xsl:text>
            </xsl:with-param>
            <xsl:with-param name="style-type">
                <xsl:value-of select="$style-type" />
            </xsl:with-param>
        </xsl:call-template>
    </xsl:variable>
    
    <xsl:value-of select="$font-w-temp" />
</xsl:when>
<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<!-- XXXXXXXXXXXXXXXXXXXXXXX ATTRIBUTO FONT-WEIGHT XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<xsl:when test="@font-weight">
      <xsl:variable name="font-w-temp">
        <xsl:value-of select="@font-weight" />
      </xsl:variable>
                                          
        <xsl:value-of select="$font-w-temp" />
</xsl:when>
<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<!-- XXXXXXXXXXXXXXXXXXXXXXX RICORSIONE XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<xsl:when test="count(ancestor::svg:*) > 0">
        <xsl:for-each select="ancestor::svg:*">
        <xsl:if test="position() = last()">
            <xsl:call-template name="valore-font-weight" />
        </xsl:if>
        </xsl:for-each>
</xsl:when>

<xsl:otherwise>
    <!-- a questo punto non abbiamo trovato font-weight in nessun elemento, 
        dobbiamo metterci un valore di default, normal!! -->
    <xsl:text>normal</xsl:text>
</xsl:otherwise>
</xsl:choose>
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: valore-text-decoration ***************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="valore-text-decoration">
<!-- cerco il valore di text-decoration, se non lo trovo nell'elemento selezionato lo cerco
    negli elementi ancestor -->

   <!-- serve per scegliere quale style applicare, se quello associato all'id, a class o 
     all'elemento, oppure nessuno dei 3 -->
    <xsl:variable name="style-type">
        <xsl:call-template name="var-style-type">
            <xsl:with-param name="attributo">
                <xsl:text>text-decoration</xsl:text>
            </xsl:with-param>
        </xsl:call-template>
    </xsl:variable>

<xsl:choose>
<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<!-- XXXXXXXXXXXXXXXXXXXXXXX GESTIONE STYLE XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<xsl:when test="(@style and (contains(@style, 'text-decoration ') or 
                contains(@style, 'text-decoration:'))) or
                (@id and substring($style-type,1,1) = 'i') or
                (@class and substring($style-type,1,1) = 'c') or
                (substring($style-type,1,1) = 'e')">
    <xsl:variable name="text-d-temp">
        <xsl:call-template name="ricerca-style">
            <xsl:with-param name="attributo">
                <xsl:text>text-decoration</xsl:text>
            </xsl:with-param>
            <xsl:with-param name="style-type">
                <xsl:value-of select="$style-type" />
            </xsl:with-param>
        </xsl:call-template>
    </xsl:variable>
    
    <xsl:value-of select="$text-d-temp" />
</xsl:when>
<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<!-- XXXXXXXXXXXXXXXXXXXXXXX ATTRIBUTO TEXT DECORATION XXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<xsl:when test="@text-decoration">
      <xsl:variable name="text-d-temp">
        <xsl:value-of select="@text-decoration" />
      </xsl:variable>
                                          
        <xsl:value-of select="$text-d-temp" />
</xsl:when>
<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<!-- XXXXXXXXXXXXXXXXXXXXXXX RICORSIONE XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<xsl:when test="count(ancestor::svg:*) > 0">
        <xsl:for-each select="ancestor::svg:*">
        <xsl:if test="position() = last()">
            <xsl:call-template name="valore-text-decoration" />
        </xsl:if>
        </xsl:for-each>
</xsl:when>

<xsl:otherwise>
    <!-- a questo punto non abbiamo trovato text-decoration in nessun elemento, 
        dobbiamo metterci un valore di default, none!! -->
    <xsl:text>none</xsl:text>
</xsl:otherwise>
</xsl:choose>
</xsl:template>


<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: valore-text-anchor ********************************* -->
<!-- ******************************************************************************** -->
<xsl:template name="valore-text-anchor">
<!-- cerco il valore di text-anchor, se non lo trovo nell'elemento selezionato lo cerco
    negli elementi ancestor -->

   <!-- serve per scegliere quale style applicare, se quello associato all'id, a class o 
     all'elemento, oppure nessuno dei 3 -->
    <xsl:variable name="style-type">
        <xsl:call-template name="var-style-type">
            <xsl:with-param name="attributo">
                <xsl:text>text-anchor</xsl:text>
            </xsl:with-param>
        </xsl:call-template>
    </xsl:variable>

<xsl:choose>
<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<!-- XXXXXXXXXXXXXXXXXXXXXXX GESTIONE STYLE XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<xsl:when test="(@style and (contains(@style, 'text-anchor ') or 
                contains(@style, 'text-anchor:'))) or
                (@id and substring($style-type,1,1) = 'i') or
                (@class and substring($style-type,1,1) = 'c') or
                (substring($style-type,1,1) = 'e')">
    <xsl:variable name="text-a-temp">
        <xsl:call-template name="ricerca-style">
            <xsl:with-param name="attributo">
                <xsl:text>text-anchor</xsl:text>
            </xsl:with-param>
            <xsl:with-param name="style-type">
                <xsl:value-of select="$style-type" />
            </xsl:with-param>
        </xsl:call-template>
    </xsl:variable>
    
    <xsl:value-of select="$text-a-temp" />
</xsl:when>
<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<!-- XXXXXXXXXXXXXXXXXXXXXXX ATTRIBUTO TEXT ANCHOR XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<xsl:when test="@text-anchor">
      <xsl:variable name="text-a-temp">
        <xsl:value-of select="@text-anchor" />
      </xsl:variable>
                                          
        <xsl:value-of select="$text-a-temp" />
</xsl:when>
<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<!-- XXXXXXXXXXXXXXXXXXXXXXX RICORSIONE XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<xsl:when test="count(ancestor::svg:*) > 0">
        <xsl:for-each select="ancestor::svg:*">
        <xsl:if test="position() = last()">
            <xsl:call-template name="valore-text-anchor" />
        </xsl:if>
        </xsl:for-each>
</xsl:when>

<xsl:otherwise>
    <!-- a questo punto non abbiamo trovato text-anchor in nessun elemento, 
        dobbiamo metterci un valore di default, start!! -->
    <xsl:text>start</xsl:text>
</xsl:otherwise>
</xsl:choose>
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: valore-font-variant ******************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="valore-font-variant">
<!-- cerco il valore di font-variant, se non lo trovo nell'elemento selezionato lo cerco
    negli elementi ancestor -->

   <!-- serve per scegliere quale style applicare, se quello associato all'id, a class o 
     all'elemento, oppure nessuno dei 3 -->
    <xsl:variable name="style-type">
        <xsl:call-template name="var-style-type">
            <xsl:with-param name="attributo">
                <xsl:text>font-variant</xsl:text>
            </xsl:with-param>
        </xsl:call-template>
    </xsl:variable>

<xsl:choose>
<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<!-- XXXXXXXXXXXXXXXXXXXXXXX GESTIONE STYLE XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<xsl:when test="(@style and (contains(@style, 'font-variant ') or 
                contains(@style, 'font-variant:'))) or
                (@id and substring($style-type,1,1) = 'i') or
                (@class and substring($style-type,1,1) = 'c') or
                (substring($style-type,1,1) = 'e')">
    <xsl:variable name="font-v-temp">
        <xsl:call-template name="ricerca-style">
            <xsl:with-param name="attributo">
                <xsl:text>font-variant</xsl:text>
            </xsl:with-param>
            <xsl:with-param name="style-type">
                <xsl:value-of select="$style-type" />
            </xsl:with-param>
        </xsl:call-template>
    </xsl:variable>
    
    <xsl:value-of select="$font-v-temp" />
</xsl:when>
<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<!-- XXXXXXXXXXXXXXXXXXXXXXX ATTRIBUTO FONT-VARIANT XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<xsl:when test="@font-variant">
      <xsl:variable name="font-v-temp">
        <xsl:value-of select="@font-variant" />
      </xsl:variable>
                                          
        <xsl:value-of select="$font-v-temp" />
</xsl:when>
<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<!-- XXXXXXXXXXXXXXXXXXXXXXX RICORSIONE XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<xsl:when test="count(ancestor::svg:*) > 0">
        <xsl:for-each select="ancestor::svg:*">
        <xsl:if test="position() = last()">
            <xsl:call-template name="valore-font-variant" />
        </xsl:if>
        </xsl:for-each>
</xsl:when>

<xsl:otherwise>
    <!-- a questo punto non abbiamo trovato font-variant in nessun elemento, 
        dobbiamo metterci un valore di default, normal!! -->
    <xsl:text>normal</xsl:text>
</xsl:otherwise>
</xsl:choose>
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: valore-font-style ******************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="valore-font-style">
<!-- cerco il valore di font-style, se non lo trovo nell'elemento selezionato lo cerco
    negli elementi ancestor -->

   <!-- serve per scegliere quale style applicare, se quello associato all'id, a class o 
     all'elemento, oppure nessuno dei 3 -->
    <xsl:variable name="style-type">
        <xsl:call-template name="var-style-type">
            <xsl:with-param name="attributo">
                <xsl:text>font-style</xsl:text>
            </xsl:with-param>
        </xsl:call-template>
    </xsl:variable>

<xsl:choose>
<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<!-- XXXXXXXXXXXXXXXXXXXXXXX GESTIONE STYLE XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<xsl:when test="(@style and (contains(@style, 'font-style ') or 
                contains(@style, 'font-style:'))) or
                (@id and substring($style-type,1,1) = 'i') or
                (@class and substring($style-type,1,1) = 'c') or
                (substring($style-type,1,1) = 'e')">
    <xsl:variable name="font-s-temp">
        <xsl:call-template name="ricerca-style">
            <xsl:with-param name="attributo">
                <xsl:text>font-style</xsl:text>
            </xsl:with-param>
            <xsl:with-param name="style-type">
                <xsl:value-of select="$style-type" />
            </xsl:with-param>
        </xsl:call-template>
    </xsl:variable>
    
    <xsl:value-of select="$font-s-temp" />
</xsl:when>
<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<!-- XXXXXXXXXXXXXXXXXXXXXXX ATTRIBUTO FONT-STYLE XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<xsl:when test="@font-style">
      <xsl:variable name="font-s-temp">
        <xsl:value-of select="@font-style" />
      </xsl:variable>
                                          
        <xsl:value-of select="$font-s-temp" />
</xsl:when>
<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<!-- XXXXXXXXXXXXXXXXXXXXXXX RICORSIONE XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<xsl:when test="count(ancestor::svg:*) > 0">
        <xsl:for-each select="ancestor::svg:*">
        <xsl:if test="position() = last()">
            <xsl:call-template name="valore-font-style" />
        </xsl:if>
        </xsl:for-each>
</xsl:when>

<xsl:otherwise>
    <!-- a questo punto non abbiamo trovato font-style in nessun elemento, 
        dobbiamo metterci un valore di default, normal!! -->
    <xsl:text>normal</xsl:text>
</xsl:otherwise>
</xsl:choose>
</xsl:template>



<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: font-w-perc **************************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="font-w-perc">
<!-- in base al valore di font-weight, si aumenta o diminuisce il font di una certa
     percentuale.
     Gestito solo il caso di bold. -->
    <xsl:param name="font-w" />
    <xsl:choose>
        <xsl:when test="$font-w = 'bold'">
            <xsl:text>0.2</xsl:text> <!-- 20% -->
        </xsl:when>
        <!-- gestire altri casi: bolder, lighter, 100 - 200 -->
        <xsl:otherwise>
            <xsl:text>0</xsl:text>
        </xsl:otherwise>
    </xsl:choose>
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: divisione-font-family ****************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="divisione-font-family">
    <!-- restituisce il valore con cui divider font-s, in base al tipo di font-family -->
    <xsl:variable name="ff">
        <xsl:call-template name="valore-font-family" />
    </xsl:variable>

    <xsl:choose>
        <xsl:when test="$ff = 'Verdana'">
            <xsl:text>2</xsl:text>
        </xsl:when>
        <xsl:when test="$ff = 'Times'">
            <xsl:text>2.5</xsl:text>
        </xsl:when>
        <xsl:otherwise>
            <xsl:text>2.3</xsl:text>
        </xsl:otherwise>
    </xsl:choose>
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: sostituzione *************************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="sostituzione">
    <xsl:param name="stringa" />
    <xsl:param name="carattere" />
    
<!-- funzione ricorsiva, restituisce carattere tante volte quante sono i caratteri in
     stringa
-->
<xsl:choose>
    <xsl:when test="string-length($stringa) > 0">
     <xsl:value-of select="$carattere" />
        <xsl:call-template name="sostituzione">
            <xsl:with-param name="stringa">
                <xsl:value-of select="substring($stringa,1,string-length($stringa) - 1)" />
             </xsl:with-param>
             <xsl:with-param name="carattere">
                <xsl:value-of select="$carattere" />
             </xsl:with-param>
        </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
    </xsl:otherwise>
</xsl:choose>
</xsl:template>



<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: svg:tspan mode:spazio ****************************** -->
<!-- ******************************************************************************** -->
<xsl:template match="svg:tspan" mode="spazio">
    <xsl:param name="carattere" />
    <xsl:param name="tspan-tref"><xsl:text>yes</xsl:text></xsl:param>
    <xsl:param name="pos"><xsl:text>-1</xsl:text></xsl:param>
    
<!-- tspan-tref mi dice se devo considerare solo gli elementi del tipo considerato
     oppure tutti e due. Se sono nell'elemento tspan o tref considero solo un tipo.
     Se sono nell'elemento text li considero tutti e due. Il problema senza di questo
     controllo riguarda i valori di pos. -->
    
<!-- chiama una funzione che per ogni carattere trovato in tspan lo sostituisce con 
     uno spazio. Mi serve per scrivere text mettendo un carattere particolare dove c'è
     tspan. Questo serve per calcolare la lunghezza del testo prima di un certo tspan -->
 
<xsl:variable name="conto">
    <xsl:choose>
    <xsl:when test="$tspan-tref = 'yes'">
        <xsl:value-of select="count(preceding-sibling::svg:tspan |
                                    preceding-sibling::svg:tref) + 1" />
    </xsl:when>
    <xsl:otherwise>
        <xsl:value-of select="count(preceding-sibling::svg:tspan) + 1" />
    </xsl:otherwise>
    </xsl:choose>
</xsl:variable>

<xsl:choose>    
<xsl:when test="($pos = '-1') or ($pos = $conto)">
    <xsl:call-template name="sostituzione">
        <xsl:with-param name="stringa">
                <xsl:call-template name="normalizza-spazi">
                    <xsl:with-param name="stringa">
                        <xsl:value-of select="concat(.,'')" />
                    </xsl:with-param>
                </xsl:call-template>
        </xsl:with-param>
        <xsl:with-param name="carattere">
            <xsl:value-of select="$carattere" />
        </xsl:with-param>
    </xsl:call-template>
</xsl:when>
<xsl:otherwise>
    <xsl:call-template name="normalizza-spazi">
        <xsl:with-param name="stringa">
            <xsl:value-of select="concat(.,' ')" />
        </xsl:with-param>
    </xsl:call-template>
</xsl:otherwise>
</xsl:choose>
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: svg:tref mode:spazio ******************************* -->
<!-- ******************************************************************************** -->
<xsl:template match="svg:tref" mode="tref-spazio">
    <xsl:param name="carattere" />
    <xsl:param name="tspan-tref"><xsl:text>yes</xsl:text></xsl:param>
    <xsl:param name="pos"><xsl:text>-1</xsl:text></xsl:param>

<!-- tspan-tref mi dice se devo considerare solo gli elementi del tipo considerato
     oppure tutti e due. Se sono nell'elemento tspan o tref considero solo un tipo.
     Se sono nell'elemento text li considero tutti e due. Il problema senza di questo
     controllo riguarda i valori di pos. -->    

<!-- chiama una funzione che per ogni carattere trovato in tref lo sostituisce con 
     uno spazio. Mi serve per scrivere text mettendo un carattere particolare dove c'è
     tref. Questo serve per calcolare la lunghezza del testo prima di un certo tspan -->
 
<xsl:variable name="conto">
    <xsl:choose>
    <xsl:when test="$tspan-tref = 'yes'">
        <xsl:value-of select="count(preceding-sibling::svg:tspan | 
                                    preceding-sibling::svg:tref) + 1" />
    </xsl:when>
    <xsl:otherwise>
        <xsl:value-of select="count(preceding-sibling::svg:tref) + 1" />
    </xsl:otherwise>
    </xsl:choose>
</xsl:variable>

<xsl:variable name="stringa">
        <xsl:variable name="nome-el">
            <xsl:value-of select="substring(@xlink:href,2)" />
        </xsl:variable>
        
        <xsl:variable name="stringa-temp">
        <xsl:for-each select="//svg:defs/*[@id]">
            <xsl:if test="@id = $nome-el">
                <xsl:value-of select="." />
            </xsl:if>    
        </xsl:for-each>
        </xsl:variable>
        
        <xsl:call-template name="normalizza-spazi">
            <xsl:with-param name="stringa">
                <xsl:value-of select="concat('',$stringa-temp,' ')" />
            </xsl:with-param>
        </xsl:call-template>
</xsl:variable>
    
 
<xsl:choose>    
<xsl:when test="($pos = '-1') or ($pos = $conto)">
    
    <xsl:call-template name="sostituzione">
        <xsl:with-param name="stringa">
            <xsl:value-of select="$stringa" />
        </xsl:with-param>
        <xsl:with-param name="carattere">
            <xsl:value-of select="$carattere" />
        </xsl:with-param>
    </xsl:call-template>
</xsl:when>
<xsl:otherwise>

    <xsl:call-template name="normalizza-spazi">
        <xsl:with-param name="stringa">
            <xsl:value-of select="concat('',$stringa,'  ')" />
        </xsl:with-param>
    </xsl:call-template>


</xsl:otherwise>
</xsl:choose>
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: svg:tref mode:spazio  ****************************** -->
<!-- ******************************************************************************** -->
<xsl:template match="svg:tref" mode="spazio">
    <xsl:param name="carattere" />
    <xsl:param name="pos" />

<!-- mi serve per visualizzare il contenuto di tref quando faccio un apply-template
     all'interno di text. -->

    <xsl:variable name="nome-el">
        <xsl:value-of select="substring-after(@xlink:href,'#')" />
    </xsl:variable>
    
    <xsl:variable name="valore-tref">
        <xsl:for-each select="//*[@id = $nome-el]">
            <xsl:value-of select="." />   
        </xsl:for-each>
    </xsl:variable>
    
    <xsl:call-template name="normalizza-spazi">
        <xsl:with-param name="stringa">
            <xsl:value-of select="concat('',$valore-tref,' ')" />           
        </xsl:with-param>
    </xsl:call-template>
    
</xsl:template>


<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: svg:textPath *************************************** -->
<!-- ******************************************************************************** -->
<!-- Questi template mi servono per far in modo che quando considero il contenuto
     dell'elemento text, non mi venga considerato il contenuto dell'elemento textPath,
     perchè quest'ultimo viene gestito separatamente rispetto al normale flusso di testo.
-->
<xsl:template match="svg:textPath" mode="spazio"></xsl:template>
<xsl:template match="svg:textPath" mode="tref-spazio"></xsl:template>
<xsl:template match="svg:textPath" mode="vuoto"></xsl:template>

</xsl:stylesheet>
