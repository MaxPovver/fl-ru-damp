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
        - svg-rect
   * match:
        - svg:rect
-->


<!-- ********************************************************************************* -->
<!-- ********************************************************************************* -->
<!-- ********************************* ELEMENTO RECT ********************************* -->
<!-- ********************************************************************************* -->
<!-- ********************************************************************************* -->
<xsl:template name="svg-rect">
    <xsl:param name="w"><xsl:text>-1</xsl:text></xsl:param>
    <xsl:param name="h"><xsl:text>-1</xsl:text></xsl:param>

<!-- il rettangolo viene gestito in maniera diversa a seconda che abbia o meno gli angoli
     arrotondati:
        - se non li ha creo un normale elemento rect
        - se li ha, potrei usare l'elemento di vml roundrect, tuttavia questo non permette
            di definire gli arrotondamenti specificando sia il raggio di x che quello di y
            (se ne specifica uno solo) e quindi la traduzione risulterebbe parziale.
            Quindi si è tradotto il rettangolo tramite un opportuno path, in grado di 
                rappresentare precisamente il rettangolo originale con gli arrotondamenti
                corretti.
-->


<xsl:choose>
<!-- rettangolo arrotondato, fatto con l'elemento path -->
<xsl:when test="@rx | @ry">
<!-- inizio path -->
<v:shape>
    <xsl:attribute name="style">
        <xsl:text>position: absolute;</xsl:text>
        <xsl:text>left: </xsl:text>
         <xsl:for-each select="ancestor::svg:svg">
            <xsl:if test="position() = last()">
                <xsl:choose>
                <xsl:when test="@viewBox">
                    <xsl:value-of select="substring-before(normalize-space(@viewBox),' ')" />
                    <xsl:text>; </xsl:text>
                    <xsl:text>top: </xsl:text>
                    <xsl:value-of select="substring-before(substring-after
                                (normalize-space(@viewBox),' '),' ')" />
                    <xsl:text>; </xsl:text>
                    <xsl:text>width: </xsl:text>
                    <xsl:value-of select="substring-before(substring-after
                        (substring-after(normalize-space(@viewBox),' '),' '),' ')" />
                    <xsl:text>; </xsl:text>
                    <xsl:text>height: </xsl:text>
                    <xsl:value-of select="substring-after(substring-after
                        (substring-after(normalize-space(@viewBox),' '),' '),' ')" />
                </xsl:when>
                <xsl:otherwise>
                    <xsl:text>0; top: 0;</xsl:text>
                    <xsl:text>width: </xsl:text>
                    <xsl:call-template name="svg-width" />
                    <xsl:text>; </xsl:text>
                    <xsl:text>height: </xsl:text>
                    <xsl:call-template name="svg-height" />
                </xsl:otherwise>
                </xsl:choose>
            </xsl:if>
        </xsl:for-each>
        <xsl:text>; </xsl:text>        
        <xsl:text>z-index: -</xsl:text><xsl:value-of select="$n-elementi" />
        <xsl:text>;</xsl:text>
    </xsl:attribute>

    <xsl:for-each select="ancestor::svg:svg">
        <xsl:if test="position() = last()">
            <xsl:call-template name="coord-origin-size" />
        </xsl:if>
    </xsl:for-each>
    
    <xsl:call-template name="attributi-paint" />
    
<v:path>
       <xsl:call-template name="attributi-style" />
       <xsl:call-template name="attributi-core" />
       <xsl:call-template name="attributi-opacity" />
       <xsl:call-template name="attributi-conditional" />
       <xsl:call-template name="attributi-graphics" />
       <xsl:call-template name="attributi-mask" />
       <xsl:call-template name="attributi-filter" />
       <xsl:call-template name="attributi-graphical-event" />
       <xsl:call-template name="attributi-cursor" />
       <xsl:call-template name="attributi-external" />
       <xsl:call-template name="attributi-clip" />
       <xsl:call-template name="attributo-title" />
       

        <!-- XXXXXXXXXXXXXXX VALORI XXXXXXXXXXXXXXXXXXXX -->
        
        <!-- w-number contiene il valore di width senza unità di misura -->
        <xsl:variable name="w-number">
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
        </xsl:variable>

        <!-- h-number contiene il valore di height senza unità di misura -->
        <xsl:variable name="h-number">
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
        </xsl:variable>
   
        <!-- var-x contiene il valore di x senza unità di misura -->
        <xsl:variable name="var-x">
            <xsl:choose>
                <xsl:when test="@x">
                    <xsl:call-template name="conversione">
                        <xsl:with-param name="attributo">
                            <xsl:value-of select="@x" />
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

        <!-- var-y contiene il valore di y senza unità di misura -->
        <xsl:variable name="var-y">
            <xsl:choose>
                <xsl:when test="@y">
                    <xsl:call-template name="conversione">
                        <xsl:with-param name="attributo">
                            <xsl:value-of select="@y" />
                        </xsl:with-param>
                        <xsl:with-param name="nome">
                            <xsl:text>y</xsl:text>
                        </xsl:with-param>
                    </xsl:call-template>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:text>0</xsl:text>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:variable>

        <!--    var-rx-temp contiene il valore di rx senza unità di misura, e 
                senza aggiustamenti -->
        <xsl:variable name="var-rx-temp">
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
                <xsl:otherwise>
                    <xsl:text>0</xsl:text>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        
        <!--    var-ry-temp contiene il valore di ry senza unità di misura, e 
                senza aggiustamenti -->
        <xsl:variable name="var-ry-temp">
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
                <xsl:otherwise>
                    <xsl:text>0</xsl:text>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:variable>

        <!-- var-rx contiene il valore di rx senza unità di misura -->
        <xsl:variable name="var-rx"> <!-- rx non può essere maggiore di width/2 
                                            oppure height/2 -->
            <xsl:choose>
                <xsl:when test="@rx"> 
                    <xsl:choose><xsl:when test="$var-rx-temp > ($w-number div 2)">
                                <xsl:value-of select="$w-number div 2" /></xsl:when>
                                <xsl:otherwise><xsl:value-of select="$var-rx-temp" />
                                </xsl:otherwise>
                    </xsl:choose>
                </xsl:when>
                
                <xsl:when test="@ry">
                    <xsl:choose><xsl:when test="$var-ry-temp > ($w-number div 2)">
                                <xsl:value-of select="$w-number div 2" /></xsl:when>
                                <xsl:otherwise><xsl:value-of select="$var-ry-temp" />
                                </xsl:otherwise>
                    </xsl:choose>
                </xsl:when>
                <xsl:otherwise>0</xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        
        <xsl:variable name="var-ry"> <!-- ry non può essere maggiore di height/2 
                                            oppure width/2 -->
            <xsl:choose>
                <xsl:when test="@ry"> 
                    <xsl:choose><xsl:when test="$var-ry-temp > ($h-number div 2)">
                                <xsl:value-of select="$h-number div 2" /></xsl:when>
                                <xsl:otherwise><xsl:value-of select="$var-ry-temp" />
                                </xsl:otherwise>
                    </xsl:choose>
                </xsl:when>
                
                <xsl:when test="@rx">
                    <xsl:choose><xsl:when test="$var-rx-temp > ($h-number div 2)">
                                <xsl:value-of select="$h-number div 2" /></xsl:when>
                                <xsl:otherwise><xsl:value-of select="$var-rx-temp" />
                                </xsl:otherwise>
                    </xsl:choose>
                </xsl:when>
                <xsl:otherwise>0</xsl:otherwise>
            </xsl:choose>
        </xsl:variable>

        <!-- attributo v -->
        
        <xsl:attribute name="v">
        <!-- Step 1-2 -->
            <xsl:text>m </xsl:text>
                                        
            <xsl:value-of select="$var-x + $var-rx" /><xsl:text>,</xsl:text>
            <xsl:value-of select="$var-y" /><xsl:text> </xsl:text>
            
            <xsl:text>l </xsl:text>
            <xsl:value-of select="$var-x + $w-number - $var-rx" />
            <xsl:text>,</xsl:text>
            <xsl:value-of select="$var-y" />
            <xsl:text> </xsl:text>
            
        <!-- Step 3: arco -->
            <xsl:text>qx </xsl:text>
            <xsl:value-of select="$var-x + $w-number" />
            <xsl:text>, </xsl:text>
            <xsl:value-of select="$var-y + $var-ry" />
            <xsl:text> </xsl:text>
            
        <!-- Step 4 -->
            <xsl:text> l </xsl:text>
            <xsl:value-of select="$var-x + $w-number" />
            <xsl:text> </xsl:text>
            <xsl:value-of select="$var-y + $h-number  - $var-ry" />
            <xsl:text> </xsl:text>
            
        <!-- Step 5: arco -->
            <xsl:text>qy </xsl:text>
            <xsl:value-of select="$var-x + $w-number - $var-rx" />
            <xsl:text>, </xsl:text>
            <xsl:value-of select="$var-y + $h-number" />
            <xsl:text> </xsl:text>
            
        <!-- Step 6 -->
            <xsl:text> l </xsl:text>
            <xsl:value-of select="$var-x + $var-rx" />
            <xsl:text> </xsl:text>
            <xsl:value-of select="$var-y + $h-number" />
            <xsl:text> </xsl:text>
            
        <!-- Step 7: arco -->
            <xsl:text>qx </xsl:text>
            <xsl:value-of select="$var-x" />
            <xsl:text>, </xsl:text>
            <xsl:value-of select="$var-y  + $h-number - $var-ry" />
            <xsl:text> </xsl:text>
            
        <!-- Step 8 -->
            <xsl:text> l </xsl:text>
            <xsl:value-of select="$var-x" />
            <xsl:text> </xsl:text>
            <xsl:value-of select="$var-y + $var-ry" />
            <xsl:text> </xsl:text>
            
        <!-- Step 9: arco -->
            <xsl:text>qy </xsl:text>
            <xsl:value-of select="$var-x + $var-rx" />
            <xsl:text>,</xsl:text>
            <xsl:value-of select="$var-y" />
            <xsl:text> xe</xsl:text>
 
        </xsl:attribute>
        <!-- fine attributo v -->
        
        
        <xsl:apply-templates />
        
 
</v:path>
</v:shape>
<!-- fine path -->
</xsl:when>

<!-- rettangolo normale, fatto con rect -->
<xsl:otherwise>
    <v:rect>
        <xsl:call-template name="attributi-dimensione-rect" />
        <xsl:call-template name="attributi-style" />
        <xsl:call-template name="attributi-core" />
        <xsl:call-template name="attributi-opacity" />
        <xsl:call-template name="attributi-conditional" />
        <xsl:call-template name="attributi-graphics" />
        <xsl:call-template name="attributi-mask" />
        <xsl:call-template name="attributi-filter" />
        <xsl:call-template name="attributi-graphical-event" />
        <xsl:call-template name="attributi-cursor" />
        <xsl:call-template name="attributi-external" />
        <xsl:call-template name="attributi-clip" />
        <xsl:call-template name="attributo-title" />
        <xsl:call-template name="attributi-paint" />
        <xsl:apply-templates />
    </v:rect>
</xsl:otherwise>
</xsl:choose>

</xsl:template>

<!-- ********************************************************************************* -->
<!-- ********************************************************************************* -->
<!-- ********************************* ELEMENTO RECT ********************************* -->
<!-- ********************************************************************************* -->
<!-- ********************************************************************************* -->
<xsl:template match="svg:rect">

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
        <xsl:call-template name="svg-rect" />
    </xsl:otherwise>
    </xsl:choose>
</xsl:template>

</xsl:stylesheet>
