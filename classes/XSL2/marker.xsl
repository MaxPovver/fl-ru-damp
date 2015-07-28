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
        - attributi-marker
        - svg-use
        - last-value
        - last-value-1
        - first-value
        - second-value
        - svg-marker
-->

 <!-- molto approssimato -->
 <!-- è stato gestito solo in parte e senza analizzare bene i valori -->
 

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: attributi marker *********************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="attributi-marker">

<xsl:variable name="s-width">
    <xsl:variable name="width-temp">
        <xsl:call-template name="stroke-width" />
    </xsl:variable>
    <xsl:choose>
        <xsl:when test="$width-temp = ''">
            <xsl:text>1</xsl:text>
        </xsl:when>
        <xsl:otherwise>
            <xsl:value-of select="$width-temp" />
        </xsl:otherwise>
    </xsl:choose>
</xsl:variable>

<xsl:variable name="divisione">
    <xsl:choose>
        <xsl:when test="name() = 'line' or name() = 'polyline' or name() = 'polyline'">
            <xsl:text>4</xsl:text>
        </xsl:when>
        <xsl:otherwise>
            <xsl:text>10</xsl:text>
        </xsl:otherwise>
    </xsl:choose>
</xsl:variable>

<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX MARKER-END XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<xsl:if test="@marker-end">

    <xsl:variable name="x-val">
        <xsl:choose>
        <xsl:when test="name() = 'path'">
                <xsl:call-template name="last-value-1">
                    <xsl:with-param name="stringa">
                        <xsl:value-of select="normalize-space(@d)" />
                    </xsl:with-param>
                </xsl:call-template>
        </xsl:when>
        <xsl:when test="name() = 'line'">
            <xsl:choose>
                <xsl:when test="@x2">
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
                    <xsl:text>0</xsl:text>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:when>
        <xsl:when test="name() = 'polyline'">
            <xsl:call-template name="last-value-1">
                <xsl:with-param name="stringa">
                    <xsl:value-of select="normalize-space(@points)" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
        <xsl:when test="name() = 'polygon'">
            <xsl:call-template name="first-value">
                <xsl:with-param name="stringa">
                    <xsl:value-of select="normalize-space(@points)" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
        <xsl:otherwise>
        </xsl:otherwise>
        </xsl:choose>
    </xsl:variable>
    
    <xsl:variable name="y-val">
        <xsl:choose>
        <xsl:when test="name() = 'path'">
                <xsl:call-template name="last-value">
                    <xsl:with-param name="stringa">
                        <xsl:value-of select="normalize-space(@d)" />
                    </xsl:with-param>
                </xsl:call-template>
        </xsl:when>
        <xsl:when test="name() = 'line'">
            <xsl:choose>
                <xsl:when test="@y2">
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
        </xsl:when>
        <xsl:when test="name() = 'polyline'">
            <xsl:call-template name="last-value">
                <xsl:with-param name="stringa">
                    <xsl:value-of select="normalize-space(@points)" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
        <xsl:when test="name() = 'polygon'">
            <xsl:call-template name="second-value">
                <xsl:with-param name="stringa">
                    <xsl:value-of select="normalize-space(@points)" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
        <xsl:otherwise>
        </xsl:otherwise>
        </xsl:choose>
    </xsl:variable>
            
            <v:group>
                <xsl:attribute name="style">
                      <xsl:text>position: absolute;</xsl:text>
                        <xsl:text>left: </xsl:text>
                            <xsl:value-of select="$x-val" />
                        <xsl:text>; </xsl:text>
                        <xsl:text>top: </xsl:text>
                            <xsl:value-of select="$y-val" />
                        <xsl:text>; </xsl:text>
                        <xsl:text>width: </xsl:text>
                            <xsl:call-template name="svg-width" />
                        <xsl:text>; </xsl:text>
                        <xsl:text>height: </xsl:text>
                            <xsl:call-template name="svg-height" />
                        <xsl:text>; </xsl:text>
                        <xsl:text>z-index: -</xsl:text>
                            <xsl:value-of select="$n-elementi" />
                        <xsl:text>;</xsl:text>
                </xsl:attribute>
                
                <xsl:variable name="nome-marker">
                    <xsl:value-of select="substring(@marker-end,6,
                                          string-length(@marker-end) - 6)" />
                </xsl:variable>
            
                <xsl:for-each select="//svg:defs/*[@id]">
                    <xsl:if test="@id = $nome-marker">
                        <xsl:if test="@viewBox">
                            <xsl:attribute name="coordorigin">
                                <!-- da impostare coi primi due valori di viewBox -->
                                <xsl:text>0 0</xsl:text>
                            </xsl:attribute>
                            <xsl:attribute name="coordsize">
                                    <xsl:value-of select="
                                    substring-before(substring-after(substring-after(
                                    normalize-space(@viewBox),' '),' '),' ')" />
                                    <xsl:text> </xsl:text>                        
                                    <xsl:value-of select="
                                    substring-after(substring-after(substring-after(
                                    normalize-space(@viewBox),' '),' '),' ')" />
                            </xsl:attribute>
                        </xsl:if>
                        <xsl:call-template name="svg-marker">
                            <xsl:with-param name="stroke-width">
                                <xsl:value-of select="$s-width" />
                            </xsl:with-param>
                            <xsl:with-param name="divisione">
                                <xsl:value-of select="$divisione" />
                            </xsl:with-param>
                        </xsl:call-template>
                    </xsl:if>    
                </xsl:for-each>

            </v:group>
</xsl:if>

<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX MARKER-START XXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<xsl:if test="@marker-start">

    <xsl:variable name="x-val">
        <xsl:choose>
        <xsl:when test="name() = 'path'">
                <xsl:call-template name="first-value">
                    <xsl:with-param name="stringa">
                        <xsl:value-of select="normalize-space(@d)" />
                    </xsl:with-param>
                </xsl:call-template>

        </xsl:when>
        <xsl:when test="name() = 'line'">
            <xsl:choose>
                <xsl:when test="@x1">
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
                    <xsl:text>0</xsl:text>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:when>
        <xsl:when test="name() = 'polyline' or name() = 'polygon'">
            <xsl:call-template name="first-value">
                <xsl:with-param name="stringa">
                    <xsl:value-of select="normalize-space(@points)" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
        <xsl:otherwise>
        </xsl:otherwise>
        </xsl:choose>
    </xsl:variable>
    
    <xsl:variable name="y-val">
        <xsl:choose>
        <xsl:when test="name() = 'path'">
                <xsl:call-template name="second-value">
                    <xsl:with-param name="stringa">
                        <xsl:value-of select="normalize-space(@d)" />
                    </xsl:with-param>
                </xsl:call-template>
        </xsl:when>
        <xsl:when test="name() = 'line'">
            <xsl:choose>
                <xsl:when test="@y1">
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
        </xsl:when>
        <xsl:when test="name() = 'polyline' or name() = 'polygon'">
            <xsl:call-template name="second-value">
                <xsl:with-param name="stringa">
                    <xsl:value-of select="normalize-space(@points)" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
        <xsl:otherwise>
        </xsl:otherwise>
        </xsl:choose>
    </xsl:variable>
            
            <v:group>
                <xsl:attribute name="style">
                      <xsl:text>position: absolute;</xsl:text>
                        <xsl:text>left: </xsl:text>
                            <xsl:value-of select="$x-val" />
                        <xsl:text>; </xsl:text>
                        <xsl:text>top: </xsl:text>
                            <xsl:value-of select="$y-val" />
                        <xsl:text>; </xsl:text>
                        <xsl:text>width: </xsl:text>
                            <xsl:call-template name="svg-width" />
                        <xsl:text>; </xsl:text>
                        <xsl:text>height: </xsl:text>
                            <xsl:call-template name="svg-height" />
                        <xsl:text>; </xsl:text>
                        <xsl:text>z-index: -</xsl:text>
                            <xsl:value-of select="$n-elementi" />
                        <xsl:text>;</xsl:text>
                </xsl:attribute>
                
                <xsl:variable name="nome-marker">
                    <xsl:value-of select="substring(@marker-start,6,
                                          string-length(@marker-start) - 6)" />
                </xsl:variable>
            
                <xsl:for-each select="//svg:defs/*[@id]">
                    <xsl:if test="@id = $nome-marker">
                        <xsl:if test="@viewBox">
                            <xsl:attribute name="coordorigin">
                                <!-- da impostare coi primi due valori di viewBox -->
                                <xsl:text>0 0</xsl:text>
                            </xsl:attribute>
                            <xsl:attribute name="coordsize">
                                    <xsl:value-of select="
                                    substring-before(substring-after(substring-after(
                                    normalize-space(@viewBox),' '),' '),' ')" />
                                    <xsl:text> </xsl:text>                        
                                    <xsl:value-of select="
                                    substring-after(substring-after(substring-after(
                                    normalize-space(@viewBox),' '),' '),' ')" />
                            </xsl:attribute>
                        </xsl:if>
                        <xsl:call-template name="svg-marker">
                            <xsl:with-param name="stroke-width">
                                <xsl:value-of select="$s-width" />
                            </xsl:with-param>
                            <xsl:with-param name="divisione">
                                <xsl:value-of select="$divisione" />
                            </xsl:with-param>
                        </xsl:call-template>
                    </xsl:if>    
                </xsl:for-each>

            </v:group>
</xsl:if>

<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX MARKER-MID XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<!-- dovrebbe mettere il marker ad ogni vertice!!! -->
<!-- difficile da gestire, bisogna risalire ad ogni punto intermedio ... -->
<!-- quindi, line non dovrebbe averlo!! -->
<xsl:if test="@marker-mid">
</xsl:if>
     

</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: last value ***************************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="last-value">
    <xsl:param name="stringa" />
<!-- restituisce il valore dell'ultima parola (o numero) all'interno di string -->
    
<xsl:choose>
    <xsl:when test="contains($stringa, ' ') or contains($stringa, ',')">
        <xsl:choose>
            <xsl:when test="contains($stringa, ' ')">
                <xsl:call-template name="last-value">
                    <xsl:with-param name="stringa">
                        <xsl:value-of select="substring-after($stringa,' ')" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
                <xsl:call-template name="last-value">
                    <xsl:with-param name="stringa">
                        <xsl:value-of select="substring-after($stringa,',')" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:when>
    <xsl:otherwise>
        <xsl:value-of select="$stringa" />
    </xsl:otherwise>
</xsl:choose>
    
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: last value - 1 ************************************* -->
<!-- ******************************************************************************** -->
<xsl:template name="last-value-1">
    <xsl:param name="stringa" />
<!-- restituisce il valore del penultimo numero all'interno di string -->
    
<xsl:choose>
    <xsl:when test="contains($stringa, ' ')">
        <xsl:choose>
            <xsl:when test="contains(substring-after($stringa,' '), ' ')">
                <xsl:call-template name="last-value-1">
                    <xsl:with-param name="stringa">
                        <xsl:value-of select="substring-after($stringa,' ')" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
                <xsl:choose>
                    <xsl:when test="contains($stringa, ',')">
                        <xsl:value-of select="substring-before(
                        substring-after($stringa, ' '),',')" />
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="substring-before($stringa,' ')" />
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:when>
    <xsl:otherwise>
        <xsl:value-of select="$stringa" />
    </xsl:otherwise>
</xsl:choose>
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: first value **************************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="first-value">
    <xsl:param name="stringa" />
<!-- restituisce il valore del primo numero (dopo M o m) all'interno di string -->
    
<xsl:choose>
    <xsl:when test="substring($stringa, 1, 1) = 'm' or substring($stringa, 1, 1) = 'M'">
        <xsl:variable name="tipo-m">
            <xsl:choose>
                <xsl:when test="substring($stringa, 1, 1) = 'm'">
                    <xsl:text>m</xsl:text>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:text>M</xsl:text>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        <xsl:variable name="stringa-temp">
            <xsl:value-of select="substring-before(normalize-space(
                                  substring-after($stringa, $tipo-m)
                                  ), ' ')" />
        </xsl:variable>
        <xsl:choose>
            <xsl:when test="contains($stringa-temp, ',')">
                <xsl:value-of select="normalize-space(
                                      substring-before($stringa-temp,','))" />
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="normalize-space($stringa-temp)" />
            </xsl:otherwise>
        </xsl:choose>
    </xsl:when>
    <xsl:otherwise>
        <!-- il path non inizia con m, dovrebbe iniziare subito con un numero -->
        <xsl:variable name="stringa-temp">
            <xsl:value-of select="substring-before(normalize-space($stringa), ' ')" />
        </xsl:variable>
        <xsl:choose>
            <xsl:when test="contains($stringa-temp,',')">
                <xsl:value-of select="substring-before($stringa-temp,',')" />
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="$stringa-temp" />
            </xsl:otherwise>
        </xsl:choose>
    </xsl:otherwise>
</xsl:choose>
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: second value *************************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="second-value">
    <xsl:param name="stringa" />
<!-- restituisce il valore del secondo numero (dopo M o m) all'interno di string -->
    
<xsl:choose>
    <xsl:when test="substring($stringa, 1, 1) = 'm' or substring($stringa, 1, 1) = 'M'">
        <xsl:variable name="tipo-m">
            <xsl:choose>
                <xsl:when test="substring($stringa, 1, 1) = 'm'">
                    <xsl:text>m</xsl:text>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:text>M</xsl:text>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        <xsl:variable name="stringa-temp">
            <xsl:value-of select="normalize-space(substring-after($stringa, $tipo-m))" />
        </xsl:variable>
        
        <xsl:choose>
            <!-- M n1,n2 L ... -->
            <xsl:when test="contains(substring-before($stringa-temp,' '), ',')">
                
                <xsl:value-of select="normalize-space(
                                      substring-after(
                                      substring-before($stringa-temp,' '),','))" />
            </xsl:when>
            <!-- M n1 n2 L ... -->
            <xsl:otherwise>
                <xsl:value-of select="normalize-space(
                            substring-before(substring-after($stringa-temp,' '),' '))" />
            </xsl:otherwise>
        </xsl:choose>
    </xsl:when>
    <xsl:otherwise>
        <!-- il path non inizia con m, dovrebbe iniziare subito con un numero -->
        <xsl:choose>
            <xsl:when test="contains(substring-before(normalize-space($stringa),' '),',')">
                <xsl:value-of select="substring-before(
                                      normalize-space(substring-after(
                                      normalize-space($stringa),',')),' ')" />
            </xsl:when>
            <xsl:otherwise>
                <xsl:variable name="stringa-temp">
                    <xsl:value-of select="normalize-space(
                                          substring-after(normalize-space($stringa),' '))" />
                </xsl:variable>
                <xsl:choose>
                    <xsl:when test="substring($stringa-temp,1,1) = ','">
                        <xsl:value-of select="substring-before(normalize-space(
                                              substring-after($stringa-temp,',')),' ')" />
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="substring-before($stringa-temp,' ')" />
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:otherwise>
</xsl:choose>
</xsl:template>

<!-- ********************************************************************************* --> 
<!-- ********************************************************************************* -->
<!-- ********************************* ELEMENTO MARKER ******************************* -->
<!-- ********************************************************************************* -->
<!-- ********************************************************************************* -->
<xsl:template name="svg-marker">
    <xsl:param name="stroke-width"><xsl:text>1</xsl:text></xsl:param>
    <xsl:param name="divisione"><xsl:text>10</xsl:text></xsl:param>

<!-- problema con l'attributo orient, per il momento non gestito -->
<!-- problema con fill e stroke ereditati da path -->

<v:group>
    <xsl:call-template name="attributi-dimensione">
        <xsl:with-param name="w">
            <xsl:text>3</xsl:text>
        </xsl:with-param>
        <xsl:with-param name="h">
            <xsl:text>5</xsl:text>
        </xsl:with-param>
    </xsl:call-template>
    
   
    <xsl:variable name="dimensione">
        <xsl:choose>
            <xsl:when test="@markerHeight">
              <xsl:call-template name="conversione">
                <xsl:with-param name="attributo">
                    <xsl:choose>
                        <xsl:when test="@markerUnits = 'strokeHeight'">
                            <xsl:choose>
                                <xsl:when test="@markerWidth > @markerHeight">
                                    <xsl:value-of select="@markerHeight" />
                                </xsl:when>
                                <xsl:otherwise>
                                    <xsl:value-of select="@markerWidth" />
                                </xsl:otherwise>
                            </xsl:choose>
                        </xsl:when>
                        <!-- se non c'è markerUnits o se è uguale a strokeWidth -->
                        <xsl:otherwise>
                            <xsl:choose>
                                <xsl:when test="@markerHeight > @markerWidth">
                                    <xsl:value-of select="@markerWidth" />
                                </xsl:when>
                                <xsl:otherwise>
                                    <xsl:value-of select="@markerHeight" />
                                </xsl:otherwise>
                            </xsl:choose>
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:with-param>
                <xsl:with-param name="nome">
                    <xsl:text>markerHeight</xsl:text>
                </xsl:with-param>
              </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
                <xsl:text>3</xsl:text>
            </xsl:otherwise>
            </xsl:choose>
    </xsl:variable>
    
    <!-- per path è 6, per line è 3 -->
    <xsl:variable name="modulo">
        <xsl:value-of select="floor($divisione div 2) + 1" />
    </xsl:variable>
    
    <xsl:variable name="scarto">
        <xsl:value-of select="ceiling($dimensione div $modulo)" />
    </xsl:variable>
    
    <xsl:variable name="coord-origin">
            <xsl:choose>
                <xsl:when test="@refX">
                    <xsl:call-template name="conversione">
                        <xsl:with-param name="attributo">
                            <xsl:value-of select="@refX" />
                        </xsl:with-param>
                        <xsl:with-param name="nome">
                            <xsl:text>refX</xsl:text>
                        </xsl:with-param>
                    </xsl:call-template>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:text>0</xsl:text>
                </xsl:otherwise> 
            </xsl:choose>
            <xsl:text> </xsl:text>
            <xsl:choose>
                <xsl:when test="@refY">
                    <xsl:call-template name="conversione">
                        <xsl:with-param name="attributo">
                            <xsl:value-of select="@refY" />
                        </xsl:with-param>
                        <xsl:with-param name="nome">
                            <xsl:text>refY</xsl:text>
                        </xsl:with-param>
                    </xsl:call-template>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:text>0</xsl:text>
                </xsl:otherwise> 
            </xsl:choose>
    </xsl:variable>
    
    <xsl:variable name="coord-size">
            <xsl:variable name="base-x">
                <xsl:value-of select="substring-before(substring-after(substring-after(
                                      normalize-space(@viewBox),' '),' '),' ')" />
            </xsl:variable>
            <xsl:variable name="base-y">
                <xsl:value-of select="substring-after(substring-after(substring-after(
                                      normalize-space(@viewBox),' '),' '),' ')" />
            </xsl:variable>
            <xsl:value-of select="($base-x * $divisione) div $stroke-width" />
            <xsl:text> </xsl:text>
            <xsl:value-of select="($base-y * $divisione) div $stroke-width" />
    </xsl:variable>
 
    <xsl:for-each select="svg:path">
        <xsl:call-template name="svg-path">
            <xsl:with-param name="coord-origin">
                <xsl:value-of select="$coord-origin" />
            </xsl:with-param>
            <xsl:with-param name="coord-size">
                <xsl:value-of select="$coord-size" />
            </xsl:with-param>
            <xsl:with-param name="w">
                <xsl:value-of select="$dimensione - $scarto" />
            </xsl:with-param>
            <xsl:with-param name="h">
                <xsl:value-of select="$dimensione" />
            </xsl:with-param>
        </xsl:call-template>
    </xsl:for-each>
</v:group>

</xsl:template>



</xsl:stylesheet>
