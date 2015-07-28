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
        - calcola-lung-prec
        - calcola-lung-element
        - caratteri-fratelli
        - lunghezza-fratelli
-->

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: calcola-lung-prec ********************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="calcola-lung-prec">
    <xsl:param name="pos-start"><xsl:text>0</xsl:text></xsl:param>
    <xsl:param name="pos-end" />
    <xsl:param name="elemento" />
    <xsl:param name="lunghezza-prec"><xsl:text>0</xsl:text></xsl:param>
    <xsl:param name="solo-caratteri"><xsl:text>no</xsl:text></xsl:param>
    
<!-- mi calcola la lunghezza della sottostringa predecente dell'elemento dato. -->
<!-- solo-caratteri: no: mi restituisce la lunghezza. 
                     si: mi restituisce il numero di caratteri -->


<xsl:choose>
    <xsl:when test="$pos-start = $pos-end">
        <xsl:value-of select="$lunghezza-prec" />
    </xsl:when>
    <xsl:otherwise>
        <xsl:variable name="father-with-X">
            <xsl:for-each select="..">
                <xsl:choose>
                <xsl:when test="$elemento = 'tspan'">
                    <xsl:apply-templates mode="spazio">
                        <xsl:with-param name="carattere">
                            <xsl:text>#</xsl:text>
                        </xsl:with-param>
                        <xsl:with-param name="pos">
                            <xsl:value-of select="$pos-end" />
                        </xsl:with-param>
                    </xsl:apply-templates>
                </xsl:when>
                <xsl:when test="$elemento = 'tref'">
                    <xsl:apply-templates mode="tref-spazio">
                        <xsl:with-param name="carattere">
                            <xsl:text>#</xsl:text>
                        </xsl:with-param>
                        <xsl:with-param name="pos">
                            <xsl:value-of select="$pos-end" />
                        </xsl:with-param>
                    </xsl:apply-templates>
                </xsl:when>
                </xsl:choose>
            </xsl:for-each>
        </xsl:variable>
        
        <xsl:variable name="father-with-X-norm">
            <xsl:call-template name="normalizza-spazi">
                <xsl:with-param name="stringa">
                    <xsl:value-of select="$father-with-X" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:variable>
        
        <xsl:variable name="caratteri-padre">
            <xsl:value-of select="string-length(substring-before(
                                 $father-with-X-norm,'#'))" />
        </xsl:variable>
        
        <xsl:variable name="font-s-padre">
            <xsl:for-each select="..">
                <xsl:call-template name="valore-font-size" />
            </xsl:for-each>
        </xsl:variable>
    
        <xsl:variable name="font-w-padre">
            <xsl:for-each select="..">
                <xsl:call-template name="font-w-perc">
                    <xsl:with-param name="font-w">
                        <xsl:call-template name="valore-font-weight" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:for-each>
        </xsl:variable>
        
        <xsl:variable name="div-ff-padre">
            <xsl:call-template name="divisione-font-family" />
        </xsl:variable>
        
        <xsl:variable name="font-s-padre-val">
            <xsl:variable name="val-base">
                <xsl:value-of select="(($font-s-padre div $div-ff-padre) + 
                                            ($font-s-padre div $div-ff-padre * 
                                             $font-w-padre ))" />
            </xsl:variable>
            <xsl:value-of select="($val-base + ($val-base * 0.1))" />
        </xsl:variable>
        
        <xsl:variable name="caratteri-fratelli">
            <xsl:call-template name="caratteri-fratelli">
                <xsl:with-param name="pos-end">
                    <xsl:value-of select="$pos-end" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:variable>

        <xsl:variable name="lunghezza-fratelli">
            <xsl:call-template name="lunghezza-fratelli">
                <xsl:with-param name="pos-end">
                    <xsl:value-of select="$pos-end" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:variable>
        
        <xsl:variable name="lunghezza-padre">
            <xsl:value-of select="($caratteri-padre - $caratteri-fratelli) * 
                                   $font-s-padre-val" />
        </xsl:variable>
        

        <xsl:choose>
            <xsl:when test="$solo-caratteri = 'no'">
                <xsl:value-of select="($lunghezza-padre + $lunghezza-fratelli) +
                ($lunghezza-padre + $lunghezza-fratelli) * 0.1" />
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="$caratteri-padre" />
            </xsl:otherwise>
        </xsl:choose>
    </xsl:otherwise>

</xsl:choose>    
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: calcola-lung-elemento ****************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="calcola-lung-elemento">
    <xsl:param name="pos" />
    <xsl:param name="solo-caratteri"><xsl:text>no</xsl:text></xsl:param>
<!-- mi calcola la lunghezza dell'elemento dato. -->
<!-- solo-caratteri: no: mi restituisce la lunghezza. 
                     si: mi restituisce il numero di caratteri -->

<xsl:choose>
<xsl:when test="$pos &gt; 0">

<xsl:for-each select="..">
<xsl:for-each select="svg:tspan | svg:tref">
<xsl:if test="position() = $pos">

 <xsl:variable name="font-s">
    <xsl:call-template name="valore-font-size" />
 </xsl:variable>
    
 <xsl:variable name="font-w">
    <xsl:call-template name="font-w-perc">
        <xsl:with-param name="font-w">
            <xsl:call-template name="valore-font-weight" />
        </xsl:with-param>
    </xsl:call-template>
 </xsl:variable>
 
 <xsl:variable name="div-ff">
    <xsl:call-template name="divisione-font-family" />
 </xsl:variable>
 
 <xsl:variable name="font-s-val">
    <xsl:variable name="val-base">
        <xsl:value-of select="(($font-s div $div-ff) + ($font-s div $div-ff * $font-w ))" />
    </xsl:variable>
    <xsl:value-of select="($val-base + ($val-base * 0.0))" />
 </xsl:variable>
 
 <xsl:variable name="elemento">
    <xsl:value-of select="name()" />
 </xsl:variable>

<xsl:variable name="testo">
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

        <xsl:choose>
            <xsl:when test="$solo-caratteri = 'no'">
                <xsl:value-of select="ceiling(string-length($testo) * $font-s-val)" />
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="string-length($testo)" />
            </xsl:otherwise>
        </xsl:choose>
        
</xsl:if>
</xsl:for-each>
</xsl:for-each>
</xsl:when>
<xsl:otherwise>
    <xsl:text>0</xsl:text>
</xsl:otherwise>
</xsl:choose>

</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: caratteri-fratelli ********************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="caratteri-fratelli">
    <xsl:param name="pos-start">1</xsl:param>
    <xsl:param name="pos-end" />
    <xsl:param name="caratteri-prec">0</xsl:param>
    
<!-- mi calcola il numero di caratteri di tutti gli elementi tspan e tref precedenti
     all'elemento tspan/tref di posizione pos-end -->
<!-- ricorsiva -->

<xsl:choose>
    <xsl:when test="$pos-start = $pos-end">
        <xsl:value-of select="$caratteri-prec" />
    </xsl:when>
    <xsl:otherwise>
        <xsl:for-each select="..">
            <xsl:choose>
                <xsl:when test="svg:tspan | svg:tref"> 
                    
                    <xsl:for-each select="svg:tspan | svg:tref">
                        <xsl:if test="position() = $pos-start">
                        
                        
                            <xsl:choose>
                            <xsl:when test="name() = 'tspan'">
                                <xsl:variable name="testo">
                                    <xsl:call-template name="normalizza-spazi">
                                        <xsl:with-param name="stringa">
                                            <xsl:value-of select="." />
                                        </xsl:with-param>
                                    </xsl:call-template>
                                </xsl:variable>
                                <xsl:call-template name="caratteri-fratelli">
                                    <xsl:with-param name="pos-end">
                                        <xsl:value-of select="$pos-end" />
                                    </xsl:with-param>
                                    <xsl:with-param name="pos-start">
                                        <xsl:value-of select="$pos-start + 1" />
                                    </xsl:with-param>
                                    <xsl:with-param name="caratteri-prec">
                                        <xsl:value-of select="$caratteri-prec + 
                                                                string-length($testo)" />
                                    </xsl:with-param>
                                </xsl:call-template>
                            </xsl:when>
                            <xsl:otherwise>
                                 <xsl:variable name="nome-el">
                                    <xsl:value-of select="substring(@xlink:href,2)" />
                                </xsl:variable>
    
                                
                                <xsl:variable name="testo">
                                    <xsl:call-template name="normalizza-spazi">
                                        <xsl:with-param name="stringa">
                                            <xsl:for-each select="//svg:defs/*[@id]">
                                                <xsl:if test="@id = $nome-el">
                                                        <xsl:value-of select="." />
                                                </xsl:if>
                                            </xsl:for-each>
                                        </xsl:with-param>
                                    </xsl:call-template>                
                                </xsl:variable>
                                
                                <xsl:call-template name="caratteri-fratelli">
                                    <xsl:with-param name="pos-end">
                                        <xsl:value-of select="$pos-end" />
                                    </xsl:with-param>
                                    <xsl:with-param name="pos-start">
                                        <xsl:value-of select="$pos-start + 1" />
                                    </xsl:with-param>
                                    <xsl:with-param name="caratteri-prec">
                                        <xsl:value-of select="$caratteri-prec + 
                                                                string-length($testo)" />
                                    </xsl:with-param>
                                </xsl:call-template>
                                
                            </xsl:otherwise>
                            </xsl:choose>
                                                
                        </xsl:if>
                    </xsl:for-each>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="$caratteri-prec" />
                </xsl:otherwise> 
            </xsl:choose>   
        </xsl:for-each>
    </xsl:otherwise>
</xsl:choose>
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: lunghezza-fratelli ********************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="lunghezza-fratelli">
    <xsl:param name="pos-start"><xsl:text>1</xsl:text></xsl:param>
    <xsl:param name="pos-end" />
    <xsl:param name="lunghezza-prec"><xsl:text>0</xsl:text></xsl:param>
    
<!-- mi calcola il numero di caratteri di tutti gli elementi tspan e tref precedenti
     all'elemento tspan/tref di posizione pos-end e il moltiplica per la loro
     dimensione -->
<!-- ricorsiva -->

<xsl:choose>
    <xsl:when test="$pos-start = $pos-end">
        <xsl:value-of select="$lunghezza-prec" />
    </xsl:when>
    <xsl:otherwise>
        <xsl:for-each select="..">
            <xsl:choose>
                <xsl:when test="svg:tspan | svg:tref"> 
                    
                    <xsl:for-each select="svg:tspan | svg:tref">
                        <xsl:if test="position() = $pos-start">
                        
                             <xsl:variable name="font-s">            
                                <xsl:call-template name="valore-font-size" />
                            </xsl:variable>
    
                            <xsl:variable name="font-w">
                                <xsl:call-template name="font-w-perc">
                                    <xsl:with-param name="font-w">
                                        <xsl:call-template name="valore-font-weight" />
                                    </xsl:with-param>
                                </xsl:call-template> 
                            </xsl:variable>
                            
                             <xsl:variable name="div-ff">
                                <xsl:call-template name="divisione-font-family" />
                             </xsl:variable>
                            
                            <xsl:variable name="font-s-val">
                                <xsl:variable name="val-base">
                                    <xsl:value-of select="(($font-s div $div-ff) + 
                                                          ($font-s div $div-ff * 
                                                            $font-w ))" />
                                </xsl:variable>
                                <xsl:value-of select="($val-base + 
                                                             ($val-base * 0.0))" />
                             </xsl:variable>
                            
                            <xsl:choose>
                            <xsl:when test="name() = 'tspan'">
                                <xsl:variable name="testo">
                                    <xsl:call-template name="normalizza-spazi">
                                        <xsl:with-param name="stringa">
                                            <xsl:value-of select="." />
                                        </xsl:with-param>
                                    </xsl:call-template>
                                </xsl:variable>
                                <xsl:call-template name="lunghezza-fratelli">
                                    <xsl:with-param name="pos-end">
                                        <xsl:value-of select="$pos-end" />
                                    </xsl:with-param>
                                    <xsl:with-param name="pos-start">
                                        <xsl:value-of select="$pos-start + 1" />
                                    </xsl:with-param>
                                    <xsl:with-param name="lunghezza-prec">
                                        <xsl:value-of select="$lunghezza-prec + 
                                                                string-length($testo) * 
                                                                $font-s-val" />
                                    </xsl:with-param>
                                </xsl:call-template>
                            </xsl:when>
                            <xsl:otherwise>
                                 <xsl:variable name="nome-el">
                                    <xsl:value-of select="substring(@xlink:href,2)" />
                                </xsl:variable>
    
                                
                                <xsl:variable name="testo">
                                    <xsl:call-template name="normalizza-spazi">
                                        <xsl:with-param name="stringa">
                                            <xsl:for-each select="//svg:defs/*[@id]">
                                                <xsl:if test="@id = $nome-el">
                                                        <xsl:value-of select="." />
                                                </xsl:if>
                                            </xsl:for-each>
                                        </xsl:with-param>
                                    </xsl:call-template>                
                                </xsl:variable>
                                
                                <xsl:call-template name="lunghezza-fratelli">
                                    <xsl:with-param name="pos-end">
                                        <xsl:value-of select="$pos-end" />
                                    </xsl:with-param>
                                    <xsl:with-param name="pos-start">
                                        <xsl:value-of select="$pos-start + 1" />
                                    </xsl:with-param>
                                    <xsl:with-param name="lunghezza-prec">
                                        <xsl:value-of select="$lunghezza-prec + 
                                                                string-length($testo)* 
                                                                $font-s-val" />
                                    </xsl:with-param>
                                </xsl:call-template>
                                
                            </xsl:otherwise>
                            </xsl:choose>
                                                
                        </xsl:if>
                    </xsl:for-each>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="$lunghezza-prec" />
                </xsl:otherwise> 
            </xsl:choose>   
        </xsl:for-each>
    </xsl:otherwise>
</xsl:choose>

</xsl:template>

</xsl:stylesheet>


   
<!-- Stylus Studio meta-information - (c) 2004-2006. Progress Software Corporation. All rights reserved.
<metaInformation>
<scenarios/><MapperMetaTag><MapperInfo srcSchemaPathIsRelative="yes" srcSchemaInterpretAsXML="no" destSchemaPath="" destSchemaRoot="" destSchemaPathIsRelative="yes" destSchemaInterpretAsXML="no"/><MapperBlockPosition></MapperBlockPosition><TemplateContext></TemplateContext><MapperFilter side="source"></MapperFilter></MapperMetaTag>
</metaInformation>
-->