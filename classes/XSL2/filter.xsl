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

<!-- in questa versione del convertitore i filtri non sono gestiti -->

<!-- INDEX:
   * template:
        - attributi-filter
        - svg-filter
-->

<!-- NON GESTITI -->

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: attributi filter *********************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="attributi-filter">

<!-- applico il filtro dell'elemento corrente -->
<xsl:if test="@filter">
    <xsl:if test="@filter != 'none'">
        <xsl:variable name="nome-filter">
            <xsl:value-of select="substring(@filter,6,string-length(@filter) - 6)" />
        </xsl:variable>
      
        <xsl:for-each select="//svg:defs/*[@id]">
            <xsl:if test="@id = $nome-filter">
                <xsl:call-template name="svg-filter" />               
            </xsl:if>    
        </xsl:for-each>
    </xsl:if>
</xsl:if>

<!-- applico i filtri degli elementi precedenti -->
<xsl:choose>
    <xsl:when test="@filter = 'none'">
    </xsl:when>
    <xsl:when test="name() = 'svg'">
    </xsl:when>
    <xsl:otherwise>
        <xsl:for-each select="..">
            <xsl:call-template name="attributi-filter" />
        </xsl:for-each>
    </xsl:otherwise>
</xsl:choose>

</xsl:template>

<!-- ********************************************************************************* --> 
<!-- ********************************************************************************* -->
<!-- ********************************* ELEMENTO FILTER ******************************* -->
<!-- ********************************************************************************* -->
<!-- ********************************************************************************* -->
<xsl:template name="svg-filter">

</xsl:template>



</xsl:stylesheet>
