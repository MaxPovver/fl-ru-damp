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
        - svg-x             valore di x (o cx) opportunamente convertito.
        - svg-y:            valore di y (o cy) opportunamente convertito.
        - svg-width         valore di width (o valore ereditato)
        - svg-height        valore di height (o valore ereditato)
        - raggio-y          valore di ry (o r) opportunamente tradotto.
        - raggio-x          valore di rx (o r) opportunamente tradotto.
        - height-of-svg     height (contenuto in vb) dell'ultimo elemento svg ancestor
        - width-of-svg      width  (contenuto in vb) dell'ultimo elemento svg ancestor
        - x-of-svg          x      (contenuto in vb) dell'ultimo elemento svg ancestor
        - y-of-svg          y      (contenuto in vb) dell'ultimo elmento svg ancestor
        - coord-xy          imposta i valori di x e di y
      TEXT:  
        - text-x
        - text-y
-->

<!-- 
    NB: differenza tra svg-w/h e h/w-of-svg: il primo cerca l'attributo width o height e se
        non lo trova cerca i valori di viewbox (o w e h) degli elementi ancestor.
        Il secondo si riferisce subito ai valori w e h di viewbox degli elementi ancestor.
-->

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: svg-x ********************************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="svg-x" >
<!-- variabili -->

<xsl:variable name="x-number">
<xsl:choose>
    <xsl:when test="@cx">
        <xsl:call-template name="conversione">
            <xsl:with-param name="attributo">
                <xsl:value-of select="@cx" />
            </xsl:with-param>
            <xsl:with-param name="nome">
                <xsl:text>cx</xsl:text>
            </xsl:with-param>
        </xsl:call-template>
    </xsl:when>
    <xsl:when test="@x">
        <xsl:call-template name="conversione">
            <xsl:with-param name="attributo">
                <xsl:call-template name="primo-valore">
                    <xsl:with-param name="stringa"> 
                        <xsl:value-of select="@x" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:with-param>
            <xsl:with-param name="nome">
                <xsl:text>x</xsl:text>
            </xsl:with-param>
        </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
        <xsl:text>0</xsl:text>
    </xsl:otherwise>
</xsl:choose>
</xsl:variable>

<!-- fine variabili -->
            
            <xsl:choose>
            <!-- XXXXXXXXXXXXX ELLIPSE XXXXXXXXXXXXXX -->
            <xsl:when test="name() = 'ellipse' or name() = 'circle'">
                        <xsl:variable name="raggio-x">
                            <xsl:call-template name="raggio-x" />
                        </xsl:variable>
                        
                        <xsl:value-of select="$x-number - $raggio-x" />
            </xsl:when>
            <!-- XXXXXXXXXXXXXX RECT XXXXXXXXXXXXXXXXXX -->
            <xsl:when test="name() = 'rect'">
                    <xsl:value-of select="$x-number" />
            </xsl:when>
            <!-- XXXXXXXXXXXXXX LINE XXXXXXXXXXXXXXXXXX -->
            <xsl:when test="name() = 'line' or name() = 'polyline' or 
                            name() = 'polygon' or name() = 'path'">
                    <xsl:text>0</xsl:text>
            </xsl:when>
            <!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
            <xsl:otherwise>
                <!-- qui dentro dovrebbe essere gestito g e ... altri -->
                <xsl:value-of select="$x-number" />
            </xsl:otherwise>
            </xsl:choose>
</xsl:template>


<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: svg-y ********************************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="svg-y" >
    
<!-- variabili -->

<xsl:variable name="y-number">
<xsl:choose>
    <xsl:when test="@cy">
        <xsl:call-template name="conversione">
            <xsl:with-param name="attributo">
                <xsl:value-of select="@cy" />
            </xsl:with-param>
            <xsl:with-param name="nome">
                <xsl:text>cy</xsl:text>
            </xsl:with-param>
        </xsl:call-template>
    </xsl:when>
        <xsl:when test="@y">
        <xsl:call-template name="conversione">
            <xsl:with-param name="attributo">
                <xsl:call-template name="primo-valore">
                    <xsl:with-param name="stringa"> 
                        <xsl:value-of select="@y" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:with-param>
            <xsl:with-param name="nome">
                <xsl:text>cy</xsl:text>
            </xsl:with-param>
        </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
        <xsl:text>0</xsl:text>
    </xsl:otherwise>
</xsl:choose>
</xsl:variable>

<!-- fine variabili -->

            <xsl:choose>
            <!-- XXXXXXXXXXXXX ELLIPSE XXXXXXXXXXXXXX -->
            <xsl:when test="name() = 'ellipse' or name() = 'circle'">

                    <xsl:variable name="raggio-y">
                        <xsl:call-template name="raggio-y" />                    
                    </xsl:variable>

                    <xsl:value-of select="$y-number - $raggio-y" />
            </xsl:when>
            <!-- XXXXXXXXXXXXXX RECT XXXXXXXXXXXXXXXXXX -->
            <xsl:when test="name() = 'rect'">
                    <xsl:value-of select="$y-number" />
            </xsl:when>
            <!-- XXXXXXXXXXXXXX LINE XXXXXXXXXXXXXXXXXX -->
            <xsl:when test="name() = 'line' or name() = 'polyline' or 
                            name() = 'polygon' or name() = 'path'">
                    <xsl:text>0</xsl:text>
            </xsl:when>
            <!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
            <xsl:otherwise>
                <!-- qui dentro dovrebbe essere gestito g e ... altri -->
                <xsl:value-of select="$y-number" />
            </xsl:otherwise>
            </xsl:choose>
</xsl:template>


<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: svg-width ****************************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="svg-width" >
<!-- valore di width dell'elemento, se non c'è mette il valore di viewBox di svg -->

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
    <xsl:when test="@viewBox">
        <xsl:value-of select="substring-before(substring-after
        (substring-after(normalize-space(@viewBox),' '),' '),' ')" />
    </xsl:when>
    <xsl:otherwise>
        <xsl:choose>
            <xsl:when test="(ancestor::svg:svg) or (ancestor::*[@viewBox])">
                <xsl:for-each select="(ancestor::svg:svg) | (ancestor::*[@viewBox])">
                    <xsl:if test="position() = last()">        
                        <xsl:choose>
                            <xsl:when test="@viewBox">
                                <xsl:value-of select="substring-before(substring-after
                                                     (substring-after(
                                                      normalize-space(
                                                      @viewBox),' '),' '),' ')" />
                            </xsl:when>
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
                                <xsl:call-template name="width-of-svg" />
                            </xsl:otherwise>
                        </xsl:choose> 
                    </xsl:if>
                </xsl:for-each>
            </xsl:when>


            <xsl:when test="name() = 'svg'">
                <xsl:value-of select="$schermo-x" />
            </xsl:when>    
            
            <xsl:otherwise> <!-- in teoria qui non ci dovrebbe mai andare -->
                <xsl:call-template name="width-of-svg" />
            </xsl:otherwise>
        </xsl:choose>
    </xsl:otherwise>
</xsl:choose>

</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: svg-height ***************************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="svg-height" >
<!-- valore di height dell'elemento, se non c'è mette il valore di viewBox di svg -->

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
    <xsl:when test="@viewBox">
        <xsl:value-of select="substring-after(substring-after
        (substring-after(normalize-space(@viewBox),' '),' '),' ')" />
    </xsl:when>
        <xsl:otherwise>
        <xsl:choose>
            <xsl:when test="(ancestor::svg:svg) or (ancestor::*[@viewBox])">
                <xsl:for-each select="(ancestor::svg:svg) | (ancestor::*[@viewBox])">
                    <xsl:if test="position() = last()">        
                        <xsl:choose>
                            <xsl:when test="@viewBox">
                                <xsl:value-of select="substring-after(substring-after
                                                     (substring-after(
                                                      normalize-space(
                                                      @viewBox),' '),' '),' ')" />
                            </xsl:when>
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
                                <xsl:call-template name="height-of-svg" />
                            </xsl:otherwise>
                        </xsl:choose> 
                    </xsl:if>
                </xsl:for-each>
            </xsl:when>
            <xsl:when test="name() = 'svg'">
                <xsl:value-of select="$schermo-y" />            
            </xsl:when>
            
            <xsl:otherwise> <!-- in teoria qui non ci dovrebbe mai andare -->
                <xsl:call-template name="height-of-svg" />
            </xsl:otherwise>
        </xsl:choose>
    </xsl:otherwise>
</xsl:choose>

</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: raggio-y ******************************************* -->
<!-- ******************************************************************************** -->
<xsl:template name="raggio-y" >
<!-- restituisce il valore del raggio (per y) per gli elementi 
    opportuni (ellipse, circle) -->
    
<xsl:choose>
    <xsl:when test="@ry">
        <xsl:call-template name="conversione">
            <xsl:with-param name="attributo">
                <xsl:value-of select="@ry" />
            </xsl:with-param>
            <xsl:with-param name="nome">
                <xsl:text>ry</xsl:text>
            </xsl:with-param>
        </xsl:call-template>
    </xsl:when>
    <xsl:when test="@r">
        <xsl:call-template name="conversione">
            <xsl:with-param name="attributo">
                <xsl:value-of select="@r" />
            </xsl:with-param>
            <xsl:with-param name="nome">
                <xsl:text>r</xsl:text>
            </xsl:with-param>
        </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
        <xsl:text>0</xsl:text>
    </xsl:otherwise>
</xsl:choose>

</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: raggio-x ******************************************* -->
<!-- ******************************************************************************** -->
<xsl:template name="raggio-x" >
<!-- restituisce il valore del raggio (per x) per gli elementi 
    opportuni (ellipse, circle) -->

<xsl:choose>
    <xsl:when test="@rx">
        <xsl:call-template name="conversione">
            <xsl:with-param name="attributo">
                <xsl:value-of select="@rx" />
            </xsl:with-param>
            <xsl:with-param name="nome">
                <xsl:text>rx</xsl:text>
            </xsl:with-param>
        </xsl:call-template>
    </xsl:when>
    <xsl:when test="@r">
        <xsl:call-template name="conversione">
            <xsl:with-param name="attributo">
                <xsl:value-of select="@r" />
            </xsl:with-param>
            <xsl:with-param name="nome">
                <xsl:text>r</xsl:text>
            </xsl:with-param>
        </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
        <xsl:text>0</xsl:text>
    </xsl:otherwise>
</xsl:choose>

</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: height of svg ************************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="height-of-svg" >
<!-- valore di height dell'elemento svg, se non c'è SCHERMO-Y -->
<xsl:choose>
    <xsl:when test="ancestor::*[@viewBox]">
        <xsl:for-each select="ancestor::*[@viewBox]">
                <xsl:if test="position() = last()">
                        <xsl:value-of select="substring-after(substring-after
                        (substring-after(normalize-space(@viewBox),' '),' '),' ')" />
                </xsl:if>
        </xsl:for-each>
    </xsl:when>
    <xsl:otherwise>
        <xsl:choose>
            <xsl:when test="ancestor::*">
                <xsl:for-each select="ancestor::*">
                        <xsl:if test="position() = last()">
                                <xsl:call-template name="svg-height" />
                        </xsl:if>
                </xsl:for-each>
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="$schermo-y" />
            </xsl:otherwise>
        </xsl:choose>
    </xsl:otherwise>
</xsl:choose>
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: width of svg *************************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="width-of-svg" >
<!-- valore di width dell'elemento svg, se non c'è SCHERMO-X -->
<xsl:choose>
    <xsl:when test="ancestor::*[@viewBox]">
        <xsl:for-each select="ancestor::*[@viewBox]">
                <xsl:if test="position() = last()">
                        <xsl:value-of select="substring-before(substring-after
                        (substring-after(normalize-space(@viewBox),' '),' '),' ')" />
                </xsl:if>
        </xsl:for-each>
    </xsl:when>
    <xsl:otherwise>
        <xsl:choose>
            <xsl:when test="ancestor::*">
                <xsl:for-each select="ancestor::*">
                    <xsl:if test="position() = last()">
                            <xsl:call-template name="svg-width" />
                    </xsl:if>
                </xsl:for-each>
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="$schermo-x" />
            </xsl:otherwise>
        </xsl:choose>
    </xsl:otherwise>
</xsl:choose>
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: x of svg *************************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="x-of-svg" >
<!-- valore di x dell'elemento svg, se non c'è 0 -->
<xsl:choose>
    <xsl:when test="(ancestor::svg:svg) or (ancestor::*[@viewBox])">
        <xsl:for-each select="(ancestor::svg:svg) | (ancestor::*[@viewBox])">
                <xsl:if test="position() = last()">
                    <xsl:choose>
                        <xsl:when test="@viewBox">
                            <xsl:value-of select="substring-before(
                                                  normalize-space(@viewBox),' ')" />
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:text>0</xsl:text>
                        </xsl:otherwise>
                    </xsl:choose> 
                </xsl:if>
        </xsl:for-each>
    </xsl:when>
    <xsl:otherwise>
        <xsl:text>0</xsl:text>
    </xsl:otherwise>
</xsl:choose>
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: y of svg *************************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="y-of-svg" >
<!-- valore di y dell'elemento svg, se non c'è 0 -->
<xsl:choose>
     <xsl:when test="(ancestor::svg:svg) or (ancestor::*[@viewBox])">
        <xsl:for-each select="(ancestor::svg:svg) | (ancestor::*[@viewBox])">
                <xsl:if test="position() = last()">
                    <xsl:choose>
                        <xsl:when test="@viewBox">
                            <xsl:value-of select="substring-before(substring-after
                                            (normalize-space(@viewBox),' '),' ')" />
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:text>0</xsl:text>
                        </xsl:otherwise>
                    </xsl:choose> 
                </xsl:if>
        </xsl:for-each>
    </xsl:when>
    <xsl:otherwise>
        <xsl:text>0</xsl:text>
    </xsl:otherwise>
</xsl:choose>
</xsl:template>

<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXX TEMPLATE DI TEXT  XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: text-x ********************************************* -->
<!-- ******************************************************************************** -->
<xsl:template name="text-x">
<!-- n-span usato solo per gli elementi text, contiene il numero di elementi tspan e tref
     precedenti al pezzo di text che stiamo consideranto -->
    <xsl:param name="n-tspan"><xsl:text>0</xsl:text></xsl:param>

<!-- restituisce il valore di x: cioè dove devo posizionare la porzione di testo
     corrente, dipende dai valori di x e dx dell'elemento corrente e da quelli
     degli elementi precedenti. Tiene conto solo di questi attributi e non 
     della lunghezza dei testi precedenti (viene gestita in seguito)
-->

<!-- variabili -->

<!-- gestione dx: 2 casi, elemento tpsan, elemento text -->
<xsl:variable name="mine-shift-x">
<xsl:choose>
    <xsl:when test="@dx">
        <xsl:call-template name="conversione">
            <xsl:with-param name="attributo">
                <xsl:call-template name="primo-valore">
                    <xsl:with-param name="stringa"> 
                        <xsl:value-of select="@dx" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:with-param>
            <xsl:with-param name="nome">
                <xsl:text>dx</xsl:text>
            </xsl:with-param>
        </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
        <xsl:text>0</xsl:text>
    </xsl:otherwise>
</xsl:choose>
</xsl:variable>

<!-- questa variabile contiene la somma di tutti i dx dei fratelli dell'elemento considerato
     (se l'elemento è tspan o tref). Se l'elemento è text contiene la somma dei dx di tutti 
     gli elementi tspan e tref precedenti al pezzo di text che stiamo rappresentando
-->
<xsl:variable name="shift-prec-sib">
    <xsl:choose>
    <xsl:when test="name() = 'tspan'">
        <xsl:choose>
            <xsl:when test="@x">
                <xsl:text>0</xsl:text>                
            </xsl:when>
            <xsl:when test="preceding-sibling::svg:tspan[@dx] | 
                            preceding-sibling::svg:tref[@dx]">
                <xsl:call-template name="somma-dx" /> 
            </xsl:when>
            <xsl:otherwise>
                <xsl:text>0</xsl:text>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:when>
    <xsl:when test="name() = 'tref'">
        <xsl:choose>
            <xsl:when test="@x">
                <xsl:text>0</xsl:text>                
            </xsl:when>
            <xsl:when test="preceding-sibling::svg:tspan[@dx] | 
                            preceding-sibling::svg:tref[@dx]">
                <xsl:call-template name="somma-dx" /> 
            </xsl:when>
            <xsl:otherwise>
                <xsl:text>0</xsl:text>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:when>
    <xsl:when test="name() = 'text'">
        <xsl:choose>
        <xsl:when test="$n-tspan = '0'">
                <xsl:text>0</xsl:text>
        </xsl:when>
        <xsl:when test="svg:tspan | svg:tref">
            <xsl:for-each select="svg:tspan | svg:tref">
                <xsl:if test="position() = $n-tspan">
                    <xsl:variable name="dx-prec">
                        <xsl:call-template name="somma-dx" />
                    </xsl:variable>
                    <xsl:choose>
                        <xsl:when test="@dx">
                            <xsl:variable name="dx-actual">
                                <xsl:call-template name="conversione">
                                    <xsl:with-param name="attributo">
                                        <xsl:call-template name="primo-valore">
                                            <xsl:with-param name="stringa"> 
                                                <xsl:value-of select="@dx" />
                                            </xsl:with-param>
                                        </xsl:call-template>
                                    </xsl:with-param>
                                    <xsl:with-param name="nome">
                                        <xsl:text>dx</xsl:text>
                                    </xsl:with-param>
                                </xsl:call-template>
                            </xsl:variable>
                
                            <xsl:value-of select="$dx-actual + $dx-prec" />
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:value-of select="$dx-prec" />
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:if>
            </xsl:for-each>
        </xsl:when>
        <!-- elemento text senza tspan -->
        <xsl:otherwise>
            <xsl:text>0</xsl:text>
        </xsl:otherwise>
        </xsl:choose>
    </xsl:when>
    <xsl:otherwise>
        <xsl:text>0</xsl:text>
    </xsl:otherwise>
    </xsl:choose>
</xsl:variable>

<!-- spostamento dovuto al dx dell'elemento corrente e a quello degli
     elementi precedenti -->
<xsl:variable name="shift-x">
    <xsl:value-of select="$mine-shift-x + $shift-prec-sib" />
</xsl:variable>

<!-- gestione dell'attributo x -->
<xsl:variable name="x-number">
<!-- 2 casi, siamo tspan oppure text -->
<xsl:choose>
    <xsl:when test="name() = 'tspan'">
        <xsl:choose>
            <xsl:when test="@x">
                <xsl:call-template name="conversione">
                    <xsl:with-param name="attributo">
                        <xsl:call-template name="primo-valore">
                            <xsl:with-param name="stringa"> 
                                <xsl:value-of select="@x" />
                            </xsl:with-param>
                        </xsl:call-template>
                    </xsl:with-param>
                    <xsl:with-param name="nome">
                        <xsl:text>x</xsl:text>
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:when>
            <xsl:when test="preceding-sibling::svg:tspan[@x] | 
                            preceding-sibling::svg:tref[@x]  |
                            preceding-sibling::svg:textPath">
                <xsl:for-each select="preceding-sibling::svg:tspan[@x] | 
                                      preceding-sibling::svg:tref[@x]  |
                                      preceding-sibling::svg:textPath">
                    <xsl:if test="position() = last()">
                        <xsl:choose>
                            <!-- l'attributo x di textPath non influisce 
                                 gli altri testi
                            -->
                            <xsl:when test="name() = 'textPath'">
                                    <xsl:text>azzera</xsl:text>
                            </xsl:when>
                            <xsl:otherwise>
                                <xsl:call-template name="primo-valore">
                                    <xsl:with-param name="stringa"> 
                                        <xsl:value-of select="@x" />
                                    </xsl:with-param>
                                </xsl:call-template>
                            </xsl:otherwise>
                        </xsl:choose>
                    </xsl:if>
                </xsl:for-each>
            </xsl:when>
            <xsl:otherwise>
                <xsl:for-each select="..">
                    <xsl:call-template name="text-x" />
                </xsl:for-each>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:when>
    <xsl:when test="name() = 'tref'">
        <xsl:choose>
            <xsl:when test="@x">
                <xsl:call-template name="conversione">
                    <xsl:with-param name="attributo">
                        <xsl:call-template name="primo-valore">
                            <xsl:with-param name="stringa"> 
                                <xsl:value-of select="@x" />
                            </xsl:with-param>
                        </xsl:call-template>
                    </xsl:with-param>
                    <xsl:with-param name="nome">
                        <xsl:text>x</xsl:text>
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:when>
            <xsl:when test="preceding-sibling::svg:tspan[@x] | 
                            preceding-sibling::svg:tref[@x]  | 
                            preceding-sibling::svg:textPath">
                <xsl:for-each select="preceding-sibling::svg:tspan[@x] | 
                                      preceding-sibling::svg:tref[@x]  |
                                      preceding-sibling::svg:textPath">
                    <xsl:if test="position() = last()">
                        <xsl:choose>
                            <xsl:when test="name() = 'textPath'">
                                    <xsl:text>azzera</xsl:text>
                            </xsl:when>
                            <xsl:otherwise>
                                <xsl:call-template name="primo-valore">
                                    <xsl:with-param name="stringa"> 
                                        <xsl:value-of select="@x" />
                                    </xsl:with-param>
                                </xsl:call-template>
                            </xsl:otherwise>
                        </xsl:choose>  
                    </xsl:if>
                </xsl:for-each>
            </xsl:when>
            <xsl:otherwise>
                <xsl:for-each select="..">
                    <xsl:call-template name="text-x" />
                </xsl:for-each>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:when>
    <!-- elemento text -->
    <xsl:otherwise>
        <xsl:choose>
            <xsl:when test="$n-tspan = '0'">
                <xsl:call-template name="svg-x" />
            </xsl:when>
            <xsl:otherwise>
                    <!-- controllo tutti i tspan con posizione minore o uguale di n-tspan
                         e considero l'ultimo che ha l'attributo x -->
                    <xsl:call-template name="x-complessa">
                        <xsl:with-param name="n-tspan">
                            <xsl:value-of select="$n-tspan" />
                        </xsl:with-param>
                    </xsl:call-template>
            </xsl:otherwise>
            
        </xsl:choose>
    </xsl:otherwise>
</xsl:choose>
</xsl:variable>

<!-- fine variabili -->
    
<xsl:choose>
    <xsl:when test="contains($x-number,'azzera')">
        <!-- serve per segnalare la presenza di un textPath con attributo x,
             non gestito, ma evenetualmente si può gestire -->
        <xsl:value-of select="concat('zero',$shift-x)" />
    </xsl:when>
    <xsl:otherwise>
            <xsl:value-of select="$shift-x + $x-number" />
    </xsl:otherwise>
</xsl:choose>

</xsl:template>


<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: text-y ********************************************* -->
<!-- ******************************************************************************** -->
<xsl:template name="text-y" >
<!-- n-span usato solo per gli elementi text, contiene il numero di elementi tspan
     precedenti al pezzo di text che stiamo consideranto -->
    <xsl:param name="n-tspan"><xsl:text>0</xsl:text></xsl:param>

    
<!-- variabili -->

<!-- gestione dy: 2 casi, elemento tpsan, elemento text -->
<xsl:variable name="mine-shift-y">
<xsl:choose>
    <xsl:when test="@dy">
        <xsl:call-template name="conversione">
            <xsl:with-param name="attributo">
                <xsl:call-template name="primo-valore">
                    <xsl:with-param name="stringa"> 
                        <xsl:value-of select="@dy" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:with-param>
            <xsl:with-param name="nome">
                <xsl:text>dy</xsl:text>
            </xsl:with-param>
        </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
        <xsl:text>0</xsl:text>
    </xsl:otherwise>
</xsl:choose>
</xsl:variable>

<!-- questa variabile contiene la somma di tutti i dy dei fratelli dell'elemento considerato
     (se l'elemento è tspan o tref). Se l'elemento è text contiene la somma dei dy di 
     tutti gli elementi tspan e tref precedenti al pezzo di text che stiamo rappresentando
-->
<xsl:variable name="shift-prec-sib">
    <xsl:choose>
    <xsl:when test="name() = 'tspan'">
        <xsl:choose>
            <xsl:when test="@y">
                <xsl:text>0</xsl:text>                
            </xsl:when>
            <xsl:when test="preceding-sibling::svg:tspan[@dy] | 
                            preceding-sibling::svg:tref[@dy]">
                <xsl:call-template name="somma-dy" /> 
            </xsl:when>
            <xsl:otherwise>
                <xsl:text>0</xsl:text>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:when>
    <xsl:when test="name() = 'tref'">
        <xsl:choose>
            <xsl:when test="@y">
                <xsl:text>0</xsl:text>                
            </xsl:when>
            <xsl:when test="preceding-sibling::svg:tspan[@dy] | 
                            preceding-sibling::svg:tref[@dy]">
                <xsl:call-template name="somma-dy" /> 
            </xsl:when>
            <xsl:otherwise>
                <xsl:text>0</xsl:text>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:when>
    <xsl:when test="name() = 'text'">
        <xsl:choose>
        <xsl:when test="$n-tspan = '0'">
                <xsl:text>0</xsl:text>
        </xsl:when>
        <xsl:when test="svg:tspan | svg:tref">
            <xsl:for-each select="svg:tspan | svg:tref">
                <xsl:if test="position() = $n-tspan">
                    <xsl:variable name="dy-prec">
                        <xsl:call-template name="somma-dy" />
                    </xsl:variable>
                    <xsl:choose>
                        <xsl:when test="@dy">
                            <xsl:variable name="dy-actual">
                                <xsl:call-template name="conversione">
                                    <xsl:with-param name="attributo">
                                        <xsl:call-template name="primo-valore">
                                            <xsl:with-param name="stringa"> 
                                                <xsl:value-of select="@dy" />
                                            </xsl:with-param>
                                        </xsl:call-template>
                                    </xsl:with-param>
                                    <xsl:with-param name="nome">
                                        <xsl:text>dy</xsl:text>
                                    </xsl:with-param>
                                </xsl:call-template>
                            </xsl:variable>
                
                            <xsl:value-of select="$dy-actual + $dy-prec" />
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:value-of select="$dy-prec" />
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:if>
            </xsl:for-each>
        </xsl:when>
        <!-- elemento text senza tspan -->
        <xsl:otherwise>
            <xsl:text>0</xsl:text>
        </xsl:otherwise>
        </xsl:choose>
    </xsl:when>
    <xsl:otherwise>
        <xsl:text>0</xsl:text>
    </xsl:otherwise>
    </xsl:choose>
</xsl:variable>

<xsl:variable name="shift-y">
    <xsl:value-of select="$mine-shift-y + $shift-prec-sib" />
</xsl:variable>

<xsl:variable name="y-number">
<!-- 2 casi, siamo tspan oppure text -->
<xsl:choose>
    <xsl:when test="name() = 'tspan'">
        <xsl:choose>
            <xsl:when test="@y">
                <xsl:call-template name="conversione">
                    <xsl:with-param name="attributo">
                        <xsl:call-template name="primo-valore">
                            <xsl:with-param name="stringa"> 
                                <xsl:value-of select="@y" />
                            </xsl:with-param>
                        </xsl:call-template>
                    </xsl:with-param>
                    <xsl:with-param name="nome">
                        <xsl:text>y</xsl:text>
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:when>
            <xsl:when test="preceding-sibling::svg:tspan[@y] | 
                            preceding-sibling::svg:tref[@y] | 
                            preceding-sibling::svg:textPath">
                <xsl:for-each select="preceding-sibling::svg:tspan[@y] | 
                                      preceding-sibling::svg:tref[@y] | 
                                      preceding-sibling::svg:textPath">
                    <xsl:if test="position() = last()">
                        <xsl:choose>
                            <xsl:when test="name() = 'textPath'">
                                    <xsl:text>azzera</xsl:text>
                            </xsl:when>
                            <xsl:otherwise>
                                <xsl:call-template name="primo-valore">
                                    <xsl:with-param name="stringa"> 
                                        <xsl:value-of select="@y" />
                                    </xsl:with-param>
                                </xsl:call-template>
                            </xsl:otherwise>
                        </xsl:choose>
                    </xsl:if>
                </xsl:for-each>
            </xsl:when>
            <xsl:otherwise>
                <xsl:for-each select="..">
                    <xsl:call-template name="text-y" />
                </xsl:for-each>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:when>
    <xsl:when test="name() = 'tref'">
        <xsl:choose>
            <xsl:when test="@y">
                <xsl:call-template name="conversione">
                    <xsl:with-param name="attributo">
                        <xsl:call-template name="primo-valore">
                            <xsl:with-param name="stringa"> 
                                <xsl:value-of select="@y" />
                            </xsl:with-param>
                        </xsl:call-template>
                    </xsl:with-param>
                    <xsl:with-param name="nome">
                        <xsl:text>y</xsl:text>
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:when>
            <xsl:when test="preceding-sibling::svg:tspan[@y] | 
                            preceding-sibling::svg:tref[@y] |
                            preceding-sibling::svg:textPath">
                <xsl:for-each select="preceding-sibling::svg:tspan[@y] | 
                                      preceding-sibling::svg:tref[@y] |
                                      preceding-sibling::svg:textPath">
                    <xsl:if test="position() = last()">
                        <xsl:choose>
                            <xsl:when test="name() = 'textPath'">
                                    <xsl:text>azzera</xsl:text>
                            </xsl:when>
                            <xsl:otherwise>
                                <xsl:call-template name="primo-valore">
                                    <xsl:with-param name="stringa"> 
                                        <xsl:value-of select="@y" />
                                    </xsl:with-param>
                                </xsl:call-template>
                            </xsl:otherwise>
                        </xsl:choose>                      
                    </xsl:if>
                </xsl:for-each>
            </xsl:when>
            <xsl:otherwise>
                <xsl:for-each select="..">
                    <xsl:call-template name="text-y" />
                </xsl:for-each>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:when>
    <xsl:otherwise>
        <xsl:choose>
            <xsl:when test="$n-tspan = '0'">
                <xsl:call-template name="svg-y" />
            </xsl:when>
            <xsl:otherwise>
                    <!-- controllo tutti i tspan con posizione minore o uguale di n-tspan
                         e considero l'ultimo che ha l'attributo y -->
                         
                    <xsl:call-template name="y-complessa">
                        <xsl:with-param name="n-tspan">
                            <xsl:value-of select="$n-tspan" />
                        </xsl:with-param>
                    </xsl:call-template>
                    
            </xsl:otherwise>
            
        </xsl:choose>
    </xsl:otherwise>
</xsl:choose>
</xsl:variable>
<!-- fine variabili -->

<xsl:choose>
    <xsl:when test="contains($y-number,'azzera')">
        <xsl:value-of select="concat('zero',$shift-y)" />
    </xsl:when>
    <xsl:otherwise>
            <xsl:value-of select="$shift-y + $y-number" />
    </xsl:otherwise>
</xsl:choose>

</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: coord x y  ***************************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="coord-xy" >
<!-- calcola i valori di left e top -->
    <xsl:text>position: absolute;</xsl:text>
    <xsl:text>left: </xsl:text>
    <xsl:call-template name="svg-x" />
    <xsl:text>; </xsl:text>
    <xsl:text>top: </xsl:text>
    <xsl:call-template name="svg-y" />
    <xsl:text>; </xsl:text>
</xsl:template>

</xsl:stylesheet>
