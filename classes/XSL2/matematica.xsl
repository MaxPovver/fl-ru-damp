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
        - sin-x
        - cos-x
        - e-x 
        - log-x
        - divido-e-n
        - divido-e-x
        - exp-x-y
-->

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: sin-x  ********************************************* -->
<!-- ******************************************************************************** -->
<xsl:template name="sin-x">
    <xsl:param name="x-val" />
    <xsl:param name="unita-di-misura"><xsl:text>gradi</xsl:text></xsl:param>
<!-- calcola il seno di x (espresso in gradi) -->
<!-- formula: sin(x) = x - (x^3)/3! + (x^5)/5! - (x^7)/7! + ... -->

    <xsl:variable name="x">
        <xsl:choose>
            <xsl:when test="$unita-di-misura = 'gradi'">
                <xsl:value-of select="($x-val div 180) * 3.141592653589" />
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="$x-val" />
            </xsl:otherwise>
        </xsl:choose>
    </xsl:variable>

    <xsl:value-of select="(round(
                           ($x - 
                          (($x * $x *$x) div 6) + 
                          (($x * $x *$x *$x *$x) div 120) -
                          (($x * $x * $x * $x * $x * $x * $x) div 5040) + 
                          (($x * $x * $x * $x * $x * $x * $x * $x * $x) div 362880) -
                          (($x * $x * $x * $x * $x * $x * $x * $x * $x * $x * $x) 
                                                                        div 39916800)
                          ) * 100)) div 100" />

</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: cos-x  ********************************************* -->
<!-- ******************************************************************************** -->
<xsl:template name="cos-x">
    <xsl:param name="x-val" />
<!-- calcola il coseno di x (espresso in gradi) -->
<!-- formula: cos(x) = sen(pi/2 - x) -->

    <xsl:variable name="x">
        <xsl:value-of select="($x-val div 180) * 3.141592653589" />
    </xsl:variable>
    
    <xsl:call-template name="sin-x">
        <xsl:with-param name="x-val">
            <xsl:value-of select="(1.570796326794 - $x)" />
        </xsl:with-param>
        <xsl:with-param name="unita-di-misura"> 
            <xsl:text>rad</xsl:text>
        </xsl:with-param>
    </xsl:call-template>
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: e-x  *********************************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="e-x">
    <xsl:param name="x-val" />
<!-- calcola e^x -->
<!-- formula: e^x = 1 + x/1! + x^2/2! + x^3/3!  + ... -->

    <xsl:variable name="x">
        <xsl:value-of select="$x-val" />
    </xsl:variable>

    <xsl:value-of select="1 + ($x div 1) + (($x * $x) div 2) + (($x * $x * $x) div 6) +
                          (($x * $x * $x * $x) div 24) + 
                          (($x * $x * $x * $x * $x) div 120) +
                          (($x * $x * $x * $x * $x * $x) div 720) +
                          (($x * $x * $x * $x * $x * $x * $x) div 5040) + 
                          (($x * $x * $x * $x * $x * $x * $x * $x) div 40320) + 
                          (($x * $x * $x * $x * $x * $x * $x * $x * $x) div 362880) + 
                          (($x * $x * $x * $x * $x * $x * $x * $x * $x * $x) div 3628800) + 
                          (($x * $x * $x * $x * $x * $x * $x * $x * $x * $x * $x)
                                                                            div 39916800) +
                          (($x * $x * $x * $x * $x * $x * $x * $x * $x * $x * $x * $x) 
                                                                            div 479001600) +
                          (($x * $x * $x * $x * $x * $x * $x * $x * $x * $x * $x * $x * $x) 
                                                                            div 6227020800) +
                          (($x * $x * $x * $x * $x * $x * $x * $x * $x * $x * $x * $x * $x 
                                                                  * $x) div 87178291200) +
                          (($x * $x * $x * $x * $x * $x * $x * $x * $x * $x * $x * $x * $x 
                                                        * $x * $x) div 1307674368000) 
                          " />
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: log-x  ********************************************* -->
<!-- ******************************************************************************** -->
<xsl:template name="log-x">
    <xsl:param name="x-val" />
<!-- ln(x) = n + ln(x') con n è il numero di volte che devo dividere x per e per
     ottenere un numero x' < 1 (x' = 1 + x" con x''<1) -->
<!-- calcola log(1+x) = log(1 + (x-val -1)) -->
<!-- formula: log(1 + x) = x - x^2/2 + x^3/3 - x^4/4  + ... -->

    <xsl:variable name="n">
        <xsl:call-template name="divido-e-n">
            <xsl:with-param name="x"><xsl:value-of select="$x-val" />
            </xsl:with-param>
            <xsl:with-param name="contatore"><xsl:text>0</xsl:text>
            </xsl:with-param>
        </xsl:call-template>    
    </xsl:variable>
    
    <xsl:variable name="x-1">
            <xsl:call-template name="divido-e-x">
            <xsl:with-param name="x"><xsl:value-of select="$x-val" />
            </xsl:with-param>
        </xsl:call-template>    
    </xsl:variable>

    <xsl:variable name="x">
        <xsl:value-of select="$x-1 - 1" />
    </xsl:variable>

    <xsl:value-of select="$n + $x - (($x * $x) div 2) + (($x * $x * $x) div 3) -
                          (($x * $x * $x * $x) div 4) + 
                          (($x * $x * $x * $x * $x) div 5) -
                          (($x * $x * $x * $x * $x * $x) div 6) +
                          (($x * $x * $x * $x * $x * $x * $x) div 7)" />
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: divido-e-n e divido-e-x **************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="divido-e-n">
     <xsl:param name="x" />
     <xsl:param name="contatore"><xsl:text>0</xsl:text></xsl:param>
     
    <xsl:variable name="e">
        <xsl:text>2.718281828</xsl:text>
    </xsl:variable>
     
     <xsl:choose>
        <xsl:when test="$x &lt; 2">
            <xsl:value-of select="$contatore" />
        </xsl:when>
        <xsl:otherwise>
            <xsl:call-template name="divido-e-n">
                <xsl:with-param name="x"><xsl:value-of select="$x div $e" />
                </xsl:with-param>
                <xsl:with-param name="contatore"><xsl:value-of select="$contatore + 1" />
                </xsl:with-param>
            </xsl:call-template>   
        </xsl:otherwise>
     </xsl:choose>
</xsl:template>

<xsl:template name="divido-e-x">
     <xsl:param name="x" />
     
    <xsl:variable name="e">
        <xsl:text>2.718281828</xsl:text>
    </xsl:variable>
     
     <xsl:choose>
        <xsl:when test="$x &lt; 2">
            <xsl:value-of select="$x" />
        </xsl:when>
        <xsl:otherwise>
            <xsl:call-template name="divido-e-x">
                <xsl:with-param name="x"><xsl:value-of select="$x div $e" />
                </xsl:with-param>
            </xsl:call-template>   
        </xsl:otherwise>
     </xsl:choose>
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: exp-x-y  ******************************************* -->
<!-- ******************************************************************************** -->
<xsl:template name="exp-x-y">
    <xsl:param name="x-val" />
    <xsl:param name="y-val"><xsl:text>0.5</xsl:text></xsl:param>
<!-- calcola x^y -->
<!-- formula: x^y = e^(y(ln(x))) -->

    <xsl:variable name="x">
        <xsl:value-of select="$x-val" />
    </xsl:variable>
    <xsl:variable name="y">
        <xsl:value-of select="$y-val" />
    </xsl:variable>
    
    <xsl:variable name="log-x">
        <xsl:call-template name="log-x">
            <xsl:with-param name="x-val">
                <xsl:value-of select="$x" />
            </xsl:with-param>
        </xsl:call-template>
    </xsl:variable>
    

    <xsl:call-template name="e-x">
        <xsl:with-param name="x-val">
            <xsl:value-of select="$y * $log-x" />
        </xsl:with-param>
    </xsl:call-template>
</xsl:template>


</xsl:stylesheet>
