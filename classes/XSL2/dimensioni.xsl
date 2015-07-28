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
        - attributi-dimensione
        - attributi-dimensione-group
        - attributi-dimensione-use
        - attributi-dimensione-rect
        - attributi-dimensione-ellipse
        - attributi-dimensione-line 
        - attributi-dimensione-polyline
        - attributi-dimensione-polygon
        - attributi-dimensione-circle
        - attributi-dimensione-text
-->


<!-- ******************************************************************************** -->
<!-- ******************************************************************************** -->
<!-- ******************************************************************************** -->
<!-- ******************************************************************************** -->
<!-- ************************** TEMPLATE ATTRIBUTI DI DIMENSIONE ******************** -->
<!-- ******************************************************************************** -->
<!-- ******************************************************************************** -->
<!-- ******************************************************************************** -->
<!-- ******************************************************************************** -->

<!-- questi template sono richiamati dai vari elementi per gestire opportunamente le
      proprietà di dimensionamento, quali x, y, width e height oppure altre 
      proprietà quali raggi di elliss o altro.
-->

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: attributi di dimensione  *************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="attributi-dimensione">
    <xsl:param name="w"><xsl:text>-1</xsl:text></xsl:param>
    <xsl:param name="h"><xsl:text>-1</xsl:text></xsl:param>
    <xsl:param name="shift-x"><xsl:text>0</xsl:text></xsl:param>
    <xsl:param name="shift-y"><xsl:text>0</xsl:text></xsl:param>

<!-- funzione generica, per impostare solo x,y,w,h: 
     - chiamata solo dall'elemento image
 -->
<!-- i parametri servono per impostare w,h o incrementare x,y. Sono dovuti ad un
      eventuale gestione di trasformazioni precedenti -->

<xsl:attribute name="style">
    <xsl:text>position: absolute; </xsl:text>
    <xsl:text>left: </xsl:text>
        <xsl:variable name="x-temp">
            <xsl:call-template name="svg-x" />
        </xsl:variable>
        <xsl:value-of select="$x-temp + $shift-x" />
    <xsl:text>; </xsl:text>
    
    <xsl:text>top: </xsl:text>
        <xsl:variable name="y-temp">
            <xsl:call-template name="svg-y" />
        </xsl:variable>
        <xsl:value-of select="$y-temp + $shift-y" />
    <xsl:text>; </xsl:text>
    
    <xsl:text>width: </xsl:text>
    <xsl:choose>
        <xsl:when test="$w != '-1'">
            <xsl:value-of select="$w" />
        </xsl:when>
        <xsl:otherwise>
            <xsl:call-template name="svg-width" />
        </xsl:otherwise>
    </xsl:choose>

    <xsl:text>; </xsl:text>
    <xsl:text>height: </xsl:text>
    <xsl:choose>
        <xsl:when test="$h != '-1'">
            <xsl:value-of select="$h" />
        </xsl:when>
        <xsl:otherwise>
            <xsl:call-template name="svg-height" />
        </xsl:otherwise>
    </xsl:choose>
    
    <xsl:text>; </xsl:text>
    <xsl:text>z-index: -</xsl:text><xsl:value-of select="$n-elementi" />
    <xsl:text>;</xsl:text>
    
</xsl:attribute>
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: attributi di dimensione group ********************** -->
<!-- ******************************************************************************** -->
<xsl:template name="attributi-dimensione-group">
    <xsl:param name="x"><xsl:text>-1</xsl:text></xsl:param>
    <xsl:param name="y"><xsl:text>-1</xsl:text></xsl:param>
    <xsl:param name="w"><xsl:text>-1</xsl:text></xsl:param>
    <xsl:param name="h"><xsl:text>-1</xsl:text></xsl:param>
    <xsl:param name="shift-x"><xsl:text>0</xsl:text></xsl:param>
    <xsl:param name="shift-y"><xsl:text>0</xsl:text></xsl:param>
    
    <!-- i parametri servono per gestire x,y,w,h in base a valori di eventuali
         trasformazioni precedenti -->

<xsl:attribute name="style">
    <xsl:text>position: absolute; </xsl:text>
    <xsl:text>left: </xsl:text>
        <xsl:variable name="x-temp">
            <xsl:choose>
            <xsl:when test="$x != '-1'">
                <xsl:value-of select="$x" />
            </xsl:when>
            <xsl:otherwise>
                <xsl:call-template name="x-of-svg" />
            </xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        <xsl:value-of select="$x-temp + $shift-x" />
    <xsl:text>; </xsl:text>
    
    <xsl:text>top: </xsl:text>
        <xsl:variable name="y-temp">
            <xsl:choose>
            <xsl:when test="$y != '-1'">
                <xsl:value-of select="$y" />
            </xsl:when>
            <xsl:otherwise>
                <xsl:call-template name="y-of-svg" />
            </xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        <xsl:value-of select="$y-temp + $shift-y" />
    <xsl:text>; </xsl:text>
    
    <xsl:text>width: </xsl:text>
    <xsl:choose>
        <xsl:when test="$w != '-1'">
            <xsl:value-of select="$w" />
        </xsl:when>
        <xsl:otherwise>
            <xsl:call-template name="svg-width" />
        </xsl:otherwise>
    </xsl:choose>

    <xsl:text>; </xsl:text>
    <xsl:text>height: </xsl:text>
    <xsl:choose>
        <xsl:when test="$h != '-1'">
            <xsl:value-of select="$h" />
        </xsl:when>
        <xsl:otherwise>
            <xsl:call-template name="svg-height" />
        </xsl:otherwise>
    </xsl:choose>
    
    <xsl:text>; </xsl:text>
    <xsl:text>z-index: -</xsl:text><xsl:value-of select="$n-elementi" />
    <xsl:text>;</xsl:text>
</xsl:attribute>
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: attributi dimensione use *************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="attributi-dimensione-use">
    <xsl:param name="x"><xsl:text>-1</xsl:text></xsl:param>
    <xsl:param name="y"><xsl:text>-1</xsl:text></xsl:param>
    <xsl:param name="w"><xsl:text>-1</xsl:text></xsl:param>
    <xsl:param name="h"><xsl:text>-1</xsl:text></xsl:param>
    <xsl:param name="shift-x"><xsl:text>0</xsl:text></xsl:param>
    <xsl:param name="shift-y"><xsl:text>0</xsl:text></xsl:param>
    
    <!-- la gestione di use è simile a quella di group, difatti per ogni use viene
          creato un opportuno gruppo al cui interno si inserisce l'elemento
          riferito -->

<xsl:attribute name="style">
    <xsl:text>position: absolute; </xsl:text>
    
    <xsl:text>left: </xsl:text>
        <xsl:variable name="x-temp">
            <xsl:call-template name="svg-x" />
        </xsl:variable>
        <xsl:variable name="vb_x">
            <xsl:choose>
            <xsl:when test="$x != '-1'">
                <xsl:text>0</xsl:text>
            </xsl:when>
            <xsl:otherwise>
                <xsl:call-template name="x-of-svg" />
            </xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        
    <xsl:value-of select="$x-temp + $shift-x + $vb_x" />
    <xsl:text>; </xsl:text>
    
    <xsl:text>top: </xsl:text>
        <xsl:variable name="y-temp">
            <xsl:call-template name="svg-y" />
        </xsl:variable>
        <xsl:variable name="vb_y">
            <xsl:choose>
            <xsl:when test="$y != '-1'">
                <xsl:text>0</xsl:text>
            </xsl:when>
            <xsl:otherwise>
                <xsl:call-template name="y-of-svg" />
            </xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        <xsl:value-of select="$y-temp + $shift-y + $vb_y" />
    <xsl:text>; </xsl:text>
    
    <xsl:text>width: </xsl:text>
    <xsl:choose>
        <xsl:when test="$w != '-1'">
            <xsl:value-of select="$w" />
        </xsl:when>
        <xsl:otherwise>
            <xsl:call-template name="svg-width" />
        </xsl:otherwise>
    </xsl:choose>

    <xsl:text>; </xsl:text>
    <xsl:text>height: </xsl:text>
    <xsl:choose>
        <xsl:when test="$h != '-1'">
            <xsl:value-of select="$h" />
        </xsl:when>
        <xsl:otherwise>
            <xsl:call-template name="svg-height" />
        </xsl:otherwise>
    </xsl:choose>
    
    <xsl:text>; </xsl:text>
    <xsl:text>z-index: -</xsl:text><xsl:value-of select="$n-elementi" />
    <xsl:text>;</xsl:text>
</xsl:attribute>
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: attributi di dimensione rect *********************** -->
<!-- ******************************************************************************** -->
<xsl:template name="attributi-dimensione-rect">
<xsl:attribute name="style">
    <xsl:text>position: absolute;</xsl:text>
    <xsl:text>left: </xsl:text>
        <xsl:call-template name="svg-x" />
    <xsl:text>; </xsl:text>
    <xsl:text>top: </xsl:text>
        <xsl:call-template name="svg-y" />
    <xsl:text>; </xsl:text>
    
    
    <xsl:text>width: </xsl:text>
    
    <xsl:choose>
    <xsl:when test="@width">
        <xsl:call-template name="conversione">
            <xsl:with-param name="attributo">
                <xsl:value-of select="@width" />
            </xsl:with-param>
            <xsl:with-param name="nome">
                <xsl:text>width</xsl:text>
            </xsl:with-param>
        </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
        <xsl:text>0</xsl:text>
    </xsl:otherwise>
    </xsl:choose>
    
    <xsl:text>; </xsl:text>
    <xsl:text>height: </xsl:text>

    <xsl:choose>
    <xsl:when test="@height">
        <xsl:call-template name="conversione">
            <xsl:with-param name="attributo">
                <xsl:value-of select="@height" />
            </xsl:with-param>
            <xsl:with-param name="nome">
                <xsl:text>height</xsl:text>
            </xsl:with-param>
        </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
        <xsl:text>0</xsl:text>
    </xsl:otherwise>
    </xsl:choose>

    <xsl:text>; </xsl:text>
    

    <xsl:text>z-index: -</xsl:text><xsl:value-of select="$n-elementi" />
    <xsl:text>;</xsl:text>
    
</xsl:attribute>


</xsl:template>


<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: attributi di dimensione ellipse ******************** -->
<!-- ******************************************************************************** -->
<xsl:template name="attributi-dimensione-ellipse">
<xsl:attribute name="style">
    <xsl:text>position: absolute;</xsl:text>
    <xsl:text>left: </xsl:text>
        <xsl:call-template name="svg-x" />
    <xsl:text>; </xsl:text>
    <xsl:text>top: </xsl:text>
        <xsl:call-template name="svg-y" />
    <xsl:text>; </xsl:text>
    
    <xsl:variable name="raggio-x">
        <xsl:call-template name="raggio-x" />
    </xsl:variable>
    <xsl:variable name="raggio-y">
        <xsl:call-template name="raggio-y" />
    </xsl:variable>
    
    <xsl:text>width: </xsl:text><xsl:value-of select="$raggio-x * 2" />
    <xsl:text>; </xsl:text>
    <xsl:text>height: </xsl:text><xsl:value-of select="$raggio-y * 2" />
    <xsl:text>; </xsl:text>
    

    <xsl:text>z-index: -</xsl:text><xsl:value-of select="$n-elementi" />
    <xsl:text>;</xsl:text>
    
</xsl:attribute>


</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: attributi di dimensione line *********************** -->
<!-- ******************************************************************************** -->
<xsl:template name="attributi-dimensione-line">
<xsl:attribute name="from">
    <xsl:choose>
    <xsl:when test="@x1" >
        <xsl:call-template name="conversione">
            <xsl:with-param name="attributo">
                <xsl:value-of select="@x1" />
            </xsl:with-param>
            <xsl:with-param name="nome">
                <xsl:text>x1</xsl:text>
            </xsl:with-param>
        </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
        <xsl:text>0 </xsl:text>
    </xsl:otherwise>
    </xsl:choose>
    <xsl:choose>
    <xsl:when test="@y1" >
        <xsl:text> </xsl:text>
        <xsl:call-template name="conversione">
            <xsl:with-param name="attributo">
                <xsl:value-of select="@y1" />
            </xsl:with-param>
            <xsl:with-param name="nome">
                <xsl:text>y1</xsl:text>
            </xsl:with-param>
        </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
        <xsl:text>0</xsl:text>
    </xsl:otherwise>
    </xsl:choose>
</xsl:attribute>
<xsl:attribute name="to">
    <xsl:choose>
    <xsl:when test="@x2" >
        <xsl:call-template name="conversione">
            <xsl:with-param name="attributo">
                <xsl:value-of select="@x2" />
            </xsl:with-param>
            <xsl:with-param name="nome">
                <xsl:text>x2</xsl:text>
            </xsl:with-param>
        </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
        <xsl:text>0 </xsl:text>
    </xsl:otherwise>
    </xsl:choose>
    <xsl:choose>
    <xsl:when test="@y2" >
        <xsl:text> </xsl:text>
        <xsl:call-template name="conversione">
            <xsl:with-param name="attributo">
                <xsl:value-of select="@y2" />
            </xsl:with-param>
            <xsl:with-param name="nome">
                <xsl:text>y2</xsl:text>
            </xsl:with-param>
        </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
        <xsl:text>0</xsl:text>
    </xsl:otherwise>
    </xsl:choose>
</xsl:attribute>  

    <xsl:attribute name="style">
        <xsl:call-template name="coord-xy" />
    </xsl:attribute>
    
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: attributi di dimensione polyline ******************* -->
<!-- ******************************************************************************** -->
<xsl:template name="attributi-dimensione-polyline">
<xsl:attribute name="points">
    <xsl:value-of select="normalize-space(@points)" />
</xsl:attribute>

    <xsl:attribute name="style">
        <xsl:call-template name="coord-xy" />
    </xsl:attribute>
    
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: attributi di dimensione polygon ******************** -->
<!-- ******************************************************************************** -->
<xsl:template name="attributi-dimensione-polygon">
<!-- NB: vml non presenta l'elemento polygon, per realizzarlo utilizzo un lemento
         polyline in cui congiungo l'ultimo punto col primo. Tuttavia, in questo modo
         emergono dei problemi, dovuti alla congiunzione (l'intersezione delle due
         linee non è perfetta), per risolvere questo, 
         al posto di congiungere l'ultimo punto solo col primo, lo faccio congiungere 
         anche col secondo.
-->
    <xsl:variable name="primi-punti">
        <xsl:call-template name="primo-valore">
            <xsl:with-param name="stringa">
                <xsl:value-of select="normalize-space(@points)" />
            </xsl:with-param>
        </xsl:call-template>
        <xsl:text>, </xsl:text>        
        <xsl:call-template name="secondo-valore">
            <xsl:with-param name="stringa">
                <xsl:value-of select="normalize-space(@points)" />
            </xsl:with-param>
        </xsl:call-template>
    </xsl:variable>
    
    <xsl:variable name="secondi-punti">
        <xsl:call-template name="primo-valore">
            <xsl:with-param name="stringa">
                <xsl:call-template name="dal-terzo-valore">
                    <xsl:with-param name="stringa">
                        <xsl:value-of select="normalize-space(@points)" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:with-param>
        </xsl:call-template>
        <xsl:text>, </xsl:text>        
        <xsl:call-template name="secondo-valore">
            <xsl:with-param name="stringa">
                <xsl:call-template name="dal-terzo-valore">
                    <xsl:with-param name="stringa">
                        <xsl:value-of select="normalize-space(@points)" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:with-param>
        </xsl:call-template>
    </xsl:variable>

    <xsl:variable name="tutti-i-punti">
        <!-- aggiungo i primi due punti, solo con uno, vengono problemi di congiunzione -->
        <xsl:value-of select="concat(normalize-space(@points),' ',$primi-punti,' ',$secondi-punti)" />
    </xsl:variable>
    <xsl:attribute name="points">
        <xsl:value-of select="$tutti-i-punti" />
    </xsl:attribute>
    
    <xsl:attribute name="style">
         <xsl:call-template name="coord-xy" />
    </xsl:attribute>
    
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: attributi di dimensione circle ********************* -->
<!-- ******************************************************************************** -->
<xsl:template name="attributi-dimensione-circle">
<xsl:attribute name="style">
    <xsl:text>position: absolute;</xsl:text>
    <xsl:text>left: </xsl:text>
        <xsl:call-template name="svg-x" />
    <xsl:text>; </xsl:text>
    <xsl:text>top: </xsl:text>
        <xsl:call-template name="svg-y" />
    <xsl:text>; </xsl:text>
    
    <xsl:variable name="raggio-x">
        <xsl:call-template name="raggio-x" />
    </xsl:variable>
    <xsl:variable name="raggio-y">
        <xsl:call-template name="raggio-y" />
    </xsl:variable>
    
    <xsl:text>width: </xsl:text><xsl:value-of select="$raggio-x * 2" />
    <xsl:text>; </xsl:text>
    <xsl:text>height: </xsl:text><xsl:value-of select="$raggio-y * 2" />
    <xsl:text>; </xsl:text>
    
    <xsl:text>z-index: -</xsl:text><xsl:value-of select="$n-elementi" />
    <xsl:text>;</xsl:text>
    
</xsl:attribute>


</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: attributi di dimensione text *********************** -->
<!-- ******************************************************************************** -->
<xsl:template name="attributi-dimensione-text">
    <xsl:param name="w"><xsl:text>-1</xsl:text></xsl:param>
    <xsl:param name="h"><xsl:text>-1</xsl:text></xsl:param>
    
    <xsl:param name="n-tspan"><xsl:text>0</xsl:text></xsl:param>
    
    <xsl:param name="shift-x"><xsl:text>0</xsl:text></xsl:param>
    <xsl:param name="shift-y"><xsl:text>0</xsl:text></xsl:param>
    
    <xsl:param name="lunghezza" /> <!-- contiene la lunghezza in user unit del testo -->

<!-- n-tspan serve per gli elementi text, i quali vengono rappresentati con tanti elementi
     textshape quanti sono i tspan e tref contenuti in text. n-tspan contiente il numero di 
     tspan e tref precedenti al pezzo di text che stiamo visualizzando
-->

<!-- crea gli attributi di dimensionamento del testo: x,y,w,h -->



<xsl:attribute name="style">
    <xsl:text>position: absolute;</xsl:text>
    
<!-- problema: x e y possono avere più valori, rappresenterebbero le coordinate per ogni
    carattere!!!! Gestito solo il caso di valori singoli -->
<!-- altri attributi da gestire: dx, dy, rotate, textLenght, lenghtAdjust -->

    <xsl:text>left: </xsl:text>
        <xsl:variable name="x-temp">
            <xsl:call-template name="text-x">
                <xsl:with-param name="n-tspan">
                    <xsl:value-of select="$n-tspan" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:variable>
        
        <!-- contiene il valore di x dovuto agli elementi 
             precedenti: attributi x e dx + lunghezza dei 
             testi
        -->
        <xsl:variable name="x-temp-mod">
        <xsl:choose>
            <!-- il valore zero segnala la presenza di un elemento textPath
                 precedente, consentendo gestioni diverse -->
            <xsl:when test="contains($x-temp,'zero')">
                <xsl:variable name="t">
                    <xsl:value-of select="substring-after($x-temp,'zero')" /> 
                </xsl:variable>
                <xsl:value-of select="$t + $shift-x" /> 
            </xsl:when>
            <xsl:otherwise>
               <xsl:value-of select="$x-temp + $shift-x" /> 
            </xsl:otherwise>
        </xsl:choose>
        </xsl:variable>
        
        <xsl:variable name="text-align">
            <xsl:call-template name="svg-text-anchor" />
        </xsl:variable>
        
        <!-- gestisco text-align spostando opportunamente il testo -->

        <xsl:choose>
            <xsl:when test="$text-align = 'middle'">
                <xsl:value-of select="$x-temp-mod  - ($lunghezza div 2)" />
            </xsl:when>
            <xsl:when test="$text-align = 'end'">
                <xsl:value-of select="$x-temp-mod  - $lunghezza" />
            </xsl:when>
            <xsl:otherwise>
                    <xsl:value-of select="$x-temp-mod " />
            </xsl:otherwise>
        </xsl:choose>
    <xsl:text>; </xsl:text>
    
    <xsl:text>top: </xsl:text>
        <xsl:variable name="y-temp">
            <xsl:call-template name="text-y">
                <xsl:with-param name="n-tspan">
                    <xsl:value-of select="$n-tspan" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:variable>
        
        <xsl:choose>
            <xsl:when test="contains($y-temp,'zero')">
               <xsl:value-of select="substring-after($y-temp,'zero')" /> 
            </xsl:when>
            <xsl:otherwise>
               <xsl:value-of select="$y-temp + $shift-y" /> 
            </xsl:otherwise>
        </xsl:choose>
        
    <xsl:text>; </xsl:text>
    
    <xsl:text>width: </xsl:text>
    <xsl:choose>
        <xsl:when test="$w != '-1'">
            <xsl:value-of select="$w" />
        </xsl:when>
        <xsl:otherwise>
            <xsl:call-template name="svg-width" />
        </xsl:otherwise>
    </xsl:choose>

    <xsl:text>; </xsl:text>
    <xsl:text>height: </xsl:text>
    <xsl:choose>
        <xsl:when test="$h != '-1'">
            <xsl:value-of select="$h" />
        </xsl:when>
        <xsl:otherwise>
            <xsl:call-template name="svg-height" />
        </xsl:otherwise>
    </xsl:choose>
    
    <xsl:text>; </xsl:text>
    <xsl:text>z-index: -</xsl:text><xsl:value-of select="$n-elementi" />
    <xsl:text>;</xsl:text>

    
</xsl:attribute>
</xsl:template>

</xsl:stylesheet>
