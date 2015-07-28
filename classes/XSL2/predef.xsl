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
        - inserisci-image
        - svg-circle
        - svg-polygon
        - svg-polyline
        - svg-line
        - svg-ellipse
    * match:
        - svg:image    
        - svg:circle
        - svg:polygon
        - svg:polyline
        - svg:line
        - svg:ellipse
-->

<!-- ********************************************************************************* -->
<!-- ********************************************************************************* -->
<!-- ********************************* ELEMENTO IMAGE ******************************** -->
<!-- ********************************************************************************* -->
<!-- ********************************************************************************* -->
<xsl:template match="svg:image">

<!-- devo inserire image in un gruppo solo se c'è l'attributo scale -->
<xsl:variable name="cs">
        <xsl:call-template name="preceding-svg" />
</xsl:variable>

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
        <xsl:call-template name="inserisci-image" />
</xsl:otherwise>
</xsl:choose>

</xsl:template>

<!-- ********************************************************************************* -->
<!-- ************************** TEMPLATE per image *********************************** -->
<xsl:template name="inserisci-image">
<v:image>
    <xsl:call-template name="attributi-dimensione" />
    <xsl:call-template name="attributi-style" />
    <xsl:call-template name="attributi-core" />
    <xsl:call-template name="attributi-opacity" />
    <xsl:call-template name="attributi-conditional" />
    <xsl:call-template name="attributi-graphics" />
    <xsl:call-template name="attributi-mask" />
    <xsl:call-template name="attributi-filter" />
    <xsl:call-template name="attributi-clip" />
    <xsl:call-template name="attributi-graphical-event" />
    <xsl:call-template name="attributi-cursor" />
    <xsl:call-template name="attributi-external" />
    <xsl:call-template name="attributi-viewport" />
    <xsl:call-template name="attributi-color-profile" />
    <xsl:call-template name="attributi-xlink-embed" />
    <xsl:call-template name="attributo-title" />
    <xsl:call-template name="attributi-preserve-aspect-ratio" />
    
    <xsl:attribute name="src">
        <xsl:value-of select="@xlink:href" />
    </xsl:attribute>
    <xsl:call-template name="attributi-paint" />
    
    <xsl:apply-templates />
</v:image>
</xsl:template>

<!-- ********************************************************************************* -->
<!-- ********************************************************************************* -->
<!-- ********************************* ELEMENTO CIRCLE ******************************* -->
<!-- ********************************************************************************* -->
<!-- ********************************************************************************* -->
<xsl:template name="svg-circle">

<v:oval>

    <xsl:attribute name="ratingvalue">
        <xsl:value-of select="@ratingvalue" />
    </xsl:attribute>

    <xsl:attribute name="ratingdate">
        <xsl:value-of select="@ratingdate" />
    </xsl:attribute>

    <xsl:call-template name="attributi-dimensione-circle" />
    <xsl:call-template name="attributi-style" />
    <xsl:call-template name="attributi-core" />
    <xsl:call-template name="attributi-opacity" />
    <xsl:call-template name="attributi-conditional" />
    <xsl:call-template name="attributi-graphics" />
    <xsl:call-template name="attributi-mask" />
    <xsl:call-template name="attributi-filter" />
    <xsl:call-template name="attributi-clip" />
    <xsl:call-template name="attributi-graphical-event" />
    <xsl:call-template name="attributi-cursor" />
    <xsl:call-template name="attributi-external" />
    <xsl:call-template name="attributo-title" />
    <xsl:call-template name="attributi-paint" />
    
    <xsl:apply-templates />
</v:oval>
</xsl:template>

<!-- ********************************************************************************* -->
<!-- ************************** TEMPLATE per circle ********************************** -->
<xsl:template match="svg:circle">

<xsl:variable name="cs">
        <xsl:call-template name="preceding-svg" />
</xsl:variable>

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
        <xsl:call-template name="svg-circle" />
    </xsl:otherwise>
    </xsl:choose>
</xsl:template>

<!-- ********************************************************************************* -->
<!-- ********************************************************************************* -->
<!-- ********************************* ELEMENTO POLYGON ****************************** -->
<!-- ********************************************************************************* -->
<!-- ********************************************************************************* -->
<xsl:template name="svg-polygon">
    
<v:polyline> 
    <!-- problema: congiungere gli ultimi due punti: risolto con trucchetto,congiungo 
                   l'ultimo punto col primo e col secondo. -->
    <xsl:call-template name="attributi-dimensione-polygon" />
    <xsl:call-template name="attributi-style" />
    <xsl:call-template name="attributi-core" />
    <xsl:call-template name="attributi-opacity" />
    <xsl:call-template name="attributi-conditional" />
    <xsl:call-template name="attributi-graphics" />
    <xsl:call-template name="attributi-mask" />
    <xsl:call-template name="attributi-filter" />
    <xsl:call-template name="attributi-clip" />
    <xsl:call-template name="attributi-graphical-event" />
    <xsl:call-template name="attributi-cursor" />
    <xsl:call-template name="attributi-external" />
    <xsl:call-template name="attributo-title" />
    <xsl:call-template name="attributi-paint" />
    
    <xsl:apply-templates />

</v:polyline>

<xsl:call-template name="attributi-marker" />
    
</xsl:template>

<!-- ********************************************************************************* -->
<!-- ************************** TEMPLATE per polygon ********************************* -->
<xsl:template match="svg:polygon">

<xsl:variable name="cs">
        <xsl:call-template name="preceding-svg" />
</xsl:variable>

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
        <xsl:call-template name="svg-polygon" />
    </xsl:otherwise>
    </xsl:choose>
</xsl:template>

<!-- ********************************************************************************* -->
<!-- ********************************************************************************* -->
<!-- ********************************* ELEMENTO POLYLINE ***************************** -->
<!-- ********************************************************************************* -->
<!-- ********************************************************************************* -->
<xsl:template name="svg-polyline">
<v:polyline>
    <xsl:call-template name="attributi-dimensione-polyline" />
    <xsl:call-template name="attributi-style" />
    <xsl:call-template name="attributi-core" />
    <xsl:call-template name="attributi-opacity" />
    <xsl:call-template name="attributi-conditional" />
    <xsl:call-template name="attributi-graphics" />
    <xsl:call-template name="attributi-mask" />
    <xsl:call-template name="attributi-filter" />
    <xsl:call-template name="attributi-clip" />
    <xsl:call-template name="attributi-graphical-event" />
    <xsl:call-template name="attributi-cursor" />
    <xsl:call-template name="attributi-external" />
    <xsl:call-template name="attributo-title" />
    <xsl:call-template name="attributi-paint" />
    
    <xsl:apply-templates />
</v:polyline>

<xsl:call-template name="attributi-marker" />

</xsl:template>

<!-- ********************************************************************************* -->
<!-- ************************** TEMPLATE per polyline ******************************** -->
<xsl:template match="svg:polyline">

<xsl:variable name="cs">
        <xsl:call-template name="preceding-svg" />
</xsl:variable>

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
        <xsl:call-template name="svg-polyline" />
    </xsl:otherwise>
    </xsl:choose>
</xsl:template>

<!-- ********************************************************************************* -->
<!-- ********************************************************************************* -->
<!-- ********************************* ELEMENTO LINE ********************************* -->
<!-- ********************************************************************************* -->
<!-- ********************************************************************************* -->
<xsl:template name="svg-line">
<v:line>
    <xsl:call-template name="attributi-dimensione-line" />
    <xsl:call-template name="attributi-style" />
    <xsl:call-template name="attributi-core" />
    <xsl:call-template name="attributi-opacity" />
    <xsl:call-template name="attributi-conditional" />
    <xsl:call-template name="attributi-graphics" />
    <xsl:call-template name="attributi-mask" />
    <xsl:call-template name="attributi-filter" />
    <xsl:call-template name="attributi-clip" />
    <xsl:call-template name="attributi-graphical-event" />
    <xsl:call-template name="attributi-cursor" />
    <xsl:call-template name="attributi-external" />
    <xsl:call-template name="attributo-title" />
    <xsl:call-template name="attributi-paint" />
    
    <xsl:apply-templates />
</v:line>

<xsl:call-template name="attributi-marker" />

</xsl:template>

<!-- ********************************************************************************* -->
<!-- ************************** TEMPLATE per line ************************************ -->
<xsl:template match="svg:line">

<xsl:variable name="cs">
        <xsl:call-template name="preceding-svg" />
</xsl:variable>

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
        <xsl:call-template name="svg-line" />
    </xsl:otherwise>
    </xsl:choose>
</xsl:template>

<!-- ********************************************************************************* -->
<!-- ********************************************************************************* -->
<!-- ********************************* ELEMENTO ELLIPSE ****************************** -->
<!-- ********************************************************************************* -->
<!-- ********************************************************************************* -->
<xsl:template name="svg-ellipse">
<v:oval>
    <xsl:call-template name="attributi-dimensione-ellipse" />
    <xsl:call-template name="attributi-style" />
    <xsl:call-template name="attributi-core" />
    <xsl:call-template name="attributi-opacity" />
    <xsl:call-template name="attributi-conditional" />
    <xsl:call-template name="attributi-graphics" />
    <xsl:call-template name="attributi-mask" />
    <xsl:call-template name="attributi-filter" />
    <xsl:call-template name="attributi-clip" />
    <xsl:call-template name="attributi-graphical-event" />
    <xsl:call-template name="attributi-cursor" />
    <xsl:call-template name="attributi-external" />
    <xsl:call-template name="attributo-title" />
    <xsl:call-template name="attributi-paint" />
    
    <xsl:apply-templates />
</v:oval>
</xsl:template>

<!-- ********************************************************************************* -->
<!-- ************************** TEMPLATE per ellipse ********************************* -->
<xsl:template match="svg:ellipse">

<xsl:variable name="cs">
        <xsl:call-template name="preceding-svg" />
</xsl:variable>

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
        <xsl:call-template name="svg-ellipse" />
    </xsl:otherwise>
    </xsl:choose>
</xsl:template>

</xsl:stylesheet>
