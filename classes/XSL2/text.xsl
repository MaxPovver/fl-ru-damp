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
        - svg-text-shape
        
        - somma-dx
        - somma-dx-ric
        - somma-dy
        - somma-dy-ric
        
        - y-complessa
        - cerca-y
        - x-complessa
        - cerca-x
        
        - x-chunk
        - decremento-x
        
        - c-prec
        - c-prec-ric
        
        - normalizza-spazi
        - elimina-spazi-iniziali
-->

<!-- da gestire:
  - testi con x e y con valori multipli: vedi tspan03.svg
  - posizionamenti particolari del testi (tesi in verticale, ...)
-->

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: svg text shape ************************************* -->
<!-- ******************************************************************************** -->
<xsl:template name="svg-text-shape">
    <xsl:param name="shift-x"><xsl:text>0</xsl:text></xsl:param>
    <xsl:param name="shift-y"><xsl:text>0</xsl:text></xsl:param>
    
    <!-- n-tspan contiene il numero di tspan e tref di cui devo considerare il valore dx e 
         dy, vale solo per gli elementi text, per gli altri, il valore è zero -->
    <xsl:param name="n-tspan"><xsl:text>0</xsl:text></xsl:param>
    
    <xsl:param name="id"><xsl:text></xsl:text></xsl:param>
    <xsl:param name="testo" />
    <xsl:param name="path" />
    

<!-- gestisce ogni porzione di testo (con gli opportuni parametri passati in input) creando
     un elemento shape -->
    
    <!-- valore di font-size dell'elemento corrente -->
    <xsl:variable name="font-s">
        <xsl:call-template name="valore-font-size" />
    </xsl:variable>
    
    <!-- valore di font-weight dell'elemento corrente -->
    <xsl:variable name="font-w">
        <xsl:call-template name="font-w-perc">
            <xsl:with-param name="font-w">
                <xsl:call-template name="valore-font-weight" />
            </xsl:with-param>
        </xsl:call-template> 
    </xsl:variable>
    
    <!-- valore di aggiustamento in base al tipo di font -->
    <xsl:variable name="div-ff">
        <xsl:call-template name="divisione-font-family" />
    </xsl:variable>
    
    <!-- valore della dimensione del font, in base a font-size, font-weight e 
     al valore dell'aggiustamento -->
    <xsl:variable name="font-s-val">
        <xsl:value-of select="ceiling((($font-s div $div-ff) + 
                                       ($font-s div $div-ff * $font-w )))" />
    </xsl:variable>
    
<xsl:if test="normalize-space($testo) != ''">
<v:shape>

    
    <xsl:attribute name="id"><xsl:value-of select="$id" />
    </xsl:attribute>
    
    <!-- eventuale spostamento di x dovuto a viewbox precedenti -->
    <xsl:variable name="shift-x-plus">
        <xsl:for-each select="ancestor::svg:svg">
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
    </xsl:variable>
    
    <!-- eventuale spostamento di y dovuto a viewbox precedenti -->
    <xsl:variable name="shift-y-plus">
        <xsl:for-each select="ancestor::svg:svg">
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
    </xsl:variable>
    
    <xsl:choose>
    
    <!-- se un elemento precedente contiene una trasformazione di tipo scale, devo
         modificare i valori che ho calcolato, di x,y,w,h in base alla scala che
         è stata applicata
    -->
    <xsl:when test="ancestor::svg:g[contains(@transform,'scale')]">
                
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
       
       <!-- chiamo il template che mi crea gli attributi di dimensionamento -->
        <xsl:call-template name="attributi-dimensione-text">
            <xsl:with-param name="shift-x">
                <xsl:value-of select="$shift-x + $x-val" />
            </xsl:with-param>
            <xsl:with-param name="shift-y">
                <xsl:value-of select="$shift-y + $y-val" />
            </xsl:with-param>
            <xsl:with-param name="w">
                <xsl:value-of select="$w-val" />
            </xsl:with-param>
            <xsl:with-param name="h">
                <xsl:value-of select="$h-val" />
            </xsl:with-param>
            <xsl:with-param name="n-tspan">
                <xsl:value-of select="$n-tspan" />
            </xsl:with-param>
            <xsl:with-param name="lunghezza">
               <xsl:value-of select="string-length($testo) * $font-s-val" />
            </xsl:with-param>
        </xsl:call-template>
    
        <xsl:attribute name="coordorigin">
            <xsl:value-of select="concat($x-val,' ',$y-val)" />
        </xsl:attribute>  
        <xsl:attribute name="coordsize">
            <xsl:value-of select="concat($w-val,' ',$h-val)" />
        </xsl:attribute>  
        
    </xsl:when>
    <xsl:otherwise>
        <xsl:call-template name="attributi-dimensione-text">
            <xsl:with-param name="shift-x">
                <xsl:value-of select="$shift-x + $shift-x-plus" />
            </xsl:with-param>
            <xsl:with-param name="shift-y">
                <xsl:value-of select="$shift-y + $shift-y-plus" />
            </xsl:with-param>
            <xsl:with-param name="n-tspan">
                <xsl:value-of select="$n-tspan" />
            </xsl:with-param>
            <xsl:with-param name="lunghezza">
                <xsl:value-of select="string-length($testo) * $font-s-val" />
            </xsl:with-param>
        </xsl:call-template>
    
        <xsl:for-each select="ancestor::svg:svg">
            <xsl:if test="position() = last()">
                <xsl:call-template name="coord-origin-size" />
            </xsl:if>
        </xsl:for-each>
    
    </xsl:otherwise>
    </xsl:choose>
    
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
   
   <!-- imposto il path precedentemente calcolato -->
   <v:path textpathok="true">
        <xsl:attribute name="v">
            <xsl:value-of select="$path" />
        </xsl:attribute>
   </v:path>

<!-- NB: c'è ancora qualche problema con l'altezza, da gestire eventualmente
         qui -->

<v:textpath on="true">
        <xsl:attribute name="style">
            <!-- text-align: center serve come allineamento di default!! -->
            <!--<xsl:text>text-align: center; </xsl:text>-->
            <xsl:call-template name="attributi-font" />
            <xsl:call-template name="attributi-text" />
            <xsl:call-template name="attributi-text-content" />
        </xsl:attribute>


    <xsl:attribute name="fitpath">
        <xsl:text>f</xsl:text>
    </xsl:attribute>
    
    <!--
    <xsl:attribute name="xscale">
        <xsl:text>true</xsl:text>
    </xsl:attribute>
    -->

    <xsl:attribute name="string">
        <xsl:call-template name="normalizza-spazi">
            <xsl:with-param name="stringa">
                <xsl:value-of select="$testo" />
            </xsl:with-param>
        </xsl:call-template>
    </xsl:attribute>

</v:textpath>
</v:shape>

</xsl:if>

</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: somma dx ******************************************* -->
<!-- ******************************************************************************** -->
<xsl:template name="somma-dx">
<!-- somma il valore di dx di tutti preceding-sibling -->

<!-- non faccio subito la somma ricorsiva perchè non deve sommarmi il valore di dx
     dell'elemento corrente -->
     
<xsl:variable name="somma">
<xsl:choose>
<xsl:when test="@x">
    <xsl:text>0</xsl:text>
</xsl:when>
<xsl:when test="preceding-sibling::svg:tspan[@dx] | preceding-sibling::svg:tref[@dx]">
    <xsl:for-each select="preceding-sibling::svg:tspan | 
                          preceding-sibling::svg:tref  | 
                          preceding-sibling::svg:textPath">
        <xsl:if test="position() = last()">
            <xsl:call-template name="somma-dx-ric" />
        </xsl:if>
    </xsl:for-each>
</xsl:when>
<xsl:otherwise>
    <xsl:text>0</xsl:text>
</xsl:otherwise>
</xsl:choose>
</xsl:variable>

<xsl:value-of select="$somma" />
</xsl:template>

<xsl:template name="somma-dx-ric">
<!-- somma ricorsivamente il valore di dx dell'elemento corrente e dei
     preceding-siblling -->
     
<xsl:variable name="elemento-corr">
    <xsl:choose>
    <xsl:when test="name() = 'textPath'">
        <xsl:text>0</xsl:text>
    </xsl:when>
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

<xsl:variable name="elementi-prec">
    <xsl:choose>
    <xsl:when test="@x">
        <xsl:text>0</xsl:text>
    </xsl:when>
        <xsl:when test="name() = 'textPath'">
        <xsl:text>0</xsl:text>
    </xsl:when>
    <xsl:when test="preceding-sibling::svg:tspan[@dx] | preceding-sibling::svg:tref[@dx]">
        <xsl:for-each select="preceding-sibling::svg:tspan | 
                              preceding-sibling::svg:tref  |
                              preceding-sibling::svg:textPath ">
            <xsl:if test="position() = last()">
                <xsl:call-template name="somma-dx-ric" />
            </xsl:if>
        </xsl:for-each>
    </xsl:when>
    <xsl:otherwise>
        <xsl:text>0</xsl:text>
    </xsl:otherwise>
    </xsl:choose>
</xsl:variable>

<xsl:value-of select="$elemento-corr + $elementi-prec" />
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: somma dy ******************************************* -->
<!-- ******************************************************************************** -->
<xsl:template name="somma-dy">
<!-- somma il valore di dy di tutti preceding-sibling -->

<!-- non faccio subito la somma ricorsiva perchè non deve sommarmi il valore di dy
     dell'elemento corrente -->
     
<xsl:variable name="somma">
<xsl:choose>
<xsl:when test="@y">
    <xsl:text>0</xsl:text>
</xsl:when>
<xsl:when test="preceding-sibling::svg:tspan[@dy] | preceding-sibling::svg:tref[@dy]">
    <xsl:for-each select="preceding-sibling::svg:tspan | 
                          preceding-sibling::svg:tref | 
                          preceding-sibling::svg:textPath">
        <xsl:if test="position() = last()">
            <xsl:call-template name="somma-dy-ric" />
        </xsl:if>
    </xsl:for-each>
</xsl:when>
<xsl:otherwise>
    <xsl:text>0</xsl:text>
</xsl:otherwise>
</xsl:choose>
</xsl:variable>

<xsl:value-of select="$somma" />
</xsl:template>

<xsl:template name="somma-dy-ric">
<!-- somma ricorsivamente il valore di dy dell'elemento corrente e dei
     preceding-siblling -->

<xsl:variable name="elemento-corr">
    <xsl:choose>
    <xsl:when test="name() = 'textPath'">
        <xsl:text>0</xsl:text>
    </xsl:when>
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

<xsl:variable name="elementi-prec">
    <xsl:choose>
    <xsl:when test="@y">
        <xsl:text>0</xsl:text>
    </xsl:when>
    <xsl:when test="name() = 'textPath'">
        <xsl:text>0</xsl:text>
    </xsl:when>
    <xsl:when test="preceding-sibling::svg:tspan[@dy] | preceding-sibling::svg:tref[@dy]">
        <xsl:for-each select="preceding-sibling::svg:tspan | 
                              preceding-sibling::svg:tref | 
                              preceding-sibling::svg:textPath ">
            <xsl:if test="position() = last()">
                <xsl:call-template name="somma-dy-ric" />
            </xsl:if>
        </xsl:for-each>
    </xsl:when>
    <xsl:otherwise>
        <xsl:text>0</xsl:text>
    </xsl:otherwise>
    </xsl:choose>
</xsl:variable>

<xsl:value-of select="$elemento-corr + $elementi-prec" />
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: y complessa **************************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="y-complessa">
    <xsl:param name="n-tspan"><xsl:text>0</xsl:text></xsl:param>
    
<!-- richiamata da un pezzo di text -->
<!-- cerca tra tutti gli elementi tspan con posizione minore di n-tspan il valore y, se
     lo trova restituisce il valore dell'ultimo elemento tspan, altrimenti
     chiama il template svg-y
-->

<xsl:call-template name="cerca-y">
    <xsl:with-param name="pos">
        <xsl:value-of select="$n-tspan" />
    </xsl:with-param>
</xsl:call-template>
    
</xsl:template>

<xsl:template name="cerca-y">
    <xsl:param name="pos" />
<!-- cerca ricorsivamente il valore di y nell'elemento tspan con posizione pos, se lo
     trova lo restituisce, altrimenti va ricorsivamente nell'elemento precedente.
     Se arriva al primo elemento e non trova y, chiama svg-y (che restituisce il valore
     di y dell'elemento text
-->
<xsl:choose>
<xsl:when test="$pos = '0'">
    <xsl:call-template name="svg-y" />
</xsl:when>
<xsl:otherwise>
    <xsl:for-each select="svg:tspan | svg:tref">
        <xsl:if test="position() = $pos">
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
                <xsl:when test="preceding-sibling::svg:textPath">
                    <xsl:for-each select="preceding-sibling::svg:tspan | 
                                          preceding-sibling::svg:tref | 
                                          preceding-sibling::svg:textPath">
                        <xsl:if test="position() = last()">
                            <xsl:choose>
                            <xsl:when test="name() = 'textPath'">
                                <xsl:text>0</xsl:text>
                            </xsl:when>
                            <xsl:otherwise> <!-- forse -->
                                <xsl:for-each select="..">
                                    <xsl:call-template name="cerca-y">
                                        <xsl:with-param name="pos">
                                            <xsl:value-of select="$pos - 1" />
                                        </xsl:with-param>
                                    </xsl:call-template>
                                </xsl:for-each>
                            </xsl:otherwise>
                            </xsl:choose>
                        </xsl:if>
                    </xsl:for-each>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:for-each select="..">
                    <xsl:call-template name="cerca-y">
                        <xsl:with-param name="pos">
                            <xsl:value-of select="$pos - 1" />
                        </xsl:with-param>
                    </xsl:call-template>
                    </xsl:for-each>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:if>        
    </xsl:for-each>
</xsl:otherwise>
</xsl:choose>

</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: x complessa **************************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="x-complessa">
    <xsl:param name="n-tspan"><xsl:text>0</xsl:text></xsl:param>
<!-- richiamata da un pezzo di text -->
<!-- cerca tra tutti gli elementi tspan con posizione minore di n-tspan il valore x, se
     lo trova restituisce il valore dell'ultimo elemento tspan, altrimenti
     chiama il template svg-x
-->

<xsl:call-template name="cerca-x">
    <xsl:with-param name="pos">
        <xsl:value-of select="$n-tspan" />
    </xsl:with-param>
</xsl:call-template>

</xsl:template>

<xsl:template name="cerca-x">
    <xsl:param name="pos" />
<!-- cerca ricorsivamente il valore di x nell'elemento tspan o tref con posizione pos, se lo
     trova lo restituisce, altrimenti va ricorsivamente nell'elemento precedente.
     Se arriva al primo elemento e non trova x, chiama svg-x (che restituisce il valore
     di x dell'elemento text
-->
<xsl:choose>
<xsl:when test="$pos = '0'">
    <xsl:call-template name="svg-x" />
</xsl:when>
<xsl:otherwise>
    <xsl:for-each select="svg:tspan | svg:tref">
        <xsl:if test="position() = $pos">
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
                <xsl:when test="preceding-sibling::svg:textPath">
                    <xsl:for-each select="preceding-sibling::svg:tspan | 
                                          preceding-sibling::svg:tref | 
                                          preceding-sibling::svg:textPath">
                        <xsl:if test="position() = last()">
                            <xsl:choose>
                            <xsl:when test="name() = 'textPath'">
                                <xsl:text>0</xsl:text>
                            </xsl:when>
                            <xsl:otherwise> <!-- forse -->
                                <xsl:for-each select="..">
                                    <xsl:call-template name="cerca-x">
                                        <xsl:with-param name="pos">
                                            <xsl:value-of select="$pos - 1" />
                                        </xsl:with-param>
                                    </xsl:call-template>
                                </xsl:for-each>
                            </xsl:otherwise>
                            </xsl:choose>
                        </xsl:if>
                    </xsl:for-each>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:for-each select="..">
                    <xsl:call-template name="cerca-x">
                        <xsl:with-param name="pos">
                            <xsl:value-of select="$pos - 1" />
                        </xsl:with-param>
                    </xsl:call-template>
                    </xsl:for-each>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:if>        
    </xsl:for-each>
</xsl:otherwise>
</xsl:choose>
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: x chunk ******************************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="x-chunk">
    <xsl:param name="n-tspan"><xsl:text>0</xsl:text></xsl:param>
    <xsl:param name="base"><xsl:text>0</xsl:text></xsl:param>
<!-- richiamata da un pezzo di text -->
<!-- cerca tra tutti gli elementi tspan precedenti a n-tspan, l'ultimo che ha l'attributo x.
     Se lo trova calcola la lunghezza della sottostringa dall'inizio del testo fino alla 
     fine dell'elemento, poi questa quantità viene scalata dal
     valore di base.
     Se non trova nessun elemento con x, restituisce base.
-->

<xsl:choose>
<xsl:when test="$n-tspan = '0'">
    <xsl:text>0</xsl:text>
</xsl:when>
<xsl:otherwise>
    <xsl:variable name="decremento">
        <xsl:call-template name="decremento-x">
            <xsl:with-param name="pos">
                <xsl:value-of select="$n-tspan" />
            </xsl:with-param>
        </xsl:call-template>
        </xsl:variable>

    <xsl:value-of select="$base - ($decremento)" />
</xsl:otherwise>
</xsl:choose>
</xsl:template>

<xsl:template name="decremento-x">
    <xsl:param name="pos" />
    
<!-- cerca ricorsivamente il valore di x nell'elemento tspan con posizione pos, se lo
     trova la lunghezza della sottostringa che va dall'inizio del testo fino alla fine
     dell'elemento tspan considerato.
     lo restituisce, altrimenti va ricorsivamente nell'elemento precedente.
     Se arriva al primo elemento e non trova x restituisce 0.
-->

<xsl:choose>
<xsl:when test="$pos = '0'">
    <xsl:text>0</xsl:text>
</xsl:when>
<xsl:otherwise>
    <xsl:for-each select="svg:tspan | svg:tref">
        <xsl:variable name="elemento">
            <xsl:value-of select="name()" />
        </xsl:variable>
    
        <xsl:if test="position() = $pos">
            <xsl:choose>
                <xsl:when test="@x">
                    <xsl:variable name="len-prec">
                        <xsl:call-template name="calcola-lung-prec">
                            <xsl:with-param name="pos-end">
                                <xsl:value-of select="$pos" />
                            </xsl:with-param>
                            <xsl:with-param name="elemento">
                                <xsl:value-of select="$elemento" />
                            </xsl:with-param>
                        </xsl:call-template>
                    </xsl:variable>
                    
                    <xsl:value-of select="$len-prec" />          
                              
                </xsl:when>
                <xsl:when test="preceding-sibling::svg:textPath">
                    <xsl:variable name="stop">
                        <xsl:for-each select="preceding-sibling::svg:tspan | 
                                              preceding-sibling::svg:tref |
                                              preceding-sibling::svg:textPath">
                            <xsl:if test="position() = last()">
                                <xsl:choose>
                                    <xsl:when test="name() = 'textPath'">
                                        <xsl:text>si</xsl:text>
                                    </xsl:when>
                                    <xsl:otherwise>
                                        <xsl:text>no</xsl:text>
                                    </xsl:otherwise>
                                </xsl:choose>                      
                            </xsl:if>
                        </xsl:for-each>
                    </xsl:variable>
                    
                    <xsl:choose>
                    <xsl:when test="$stop = 'si'">
                        <xsl:variable name="len-prec">
                            <xsl:call-template name="calcola-lung-prec">
                                <xsl:with-param name="pos-end">
                                    <xsl:value-of select="$pos" />
                                </xsl:with-param>
                                <xsl:with-param name="elemento">
                                    <xsl:value-of select="$elemento" />
                                </xsl:with-param>
                            </xsl:call-template>
                        </xsl:variable>                
                        <xsl:value-of select="$len-prec" /> 
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:for-each select="..">
                            <xsl:call-template name="decremento-x">
                                <xsl:with-param name="pos">
                                    <xsl:value-of select="$pos - 1" />
                                </xsl:with-param>
                            </xsl:call-template>
                        </xsl:for-each>
                    </xsl:otherwise>
                    </xsl:choose>
                        
                </xsl:when>
                <xsl:otherwise>
                    <xsl:for-each select="..">
                    <xsl:call-template name="decremento-x">
                        <xsl:with-param name="pos">
                            <xsl:value-of select="$pos - 1" />
                        </xsl:with-param>
                    </xsl:call-template>
                    </xsl:for-each>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:if>        
    </xsl:for-each>
</xsl:otherwise>
</xsl:choose>
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: c-prec ********************************************* -->
<!-- ******************************************************************************** -->
<xsl:template name="c-prec">
<!-- calcola il numero di caratteri del testo dall'inizio della stringa, fino all'ultimo
     elemento tspan o tref che contiene x, precedente all'elemento considerto
-->
<xsl:variable name="pos">
<xsl:for-each select="preceding-sibling::svg:tref  |
                      preceding-sibling::svg:tspan |
                      preceding-sibling::svg:textPath">
    <xsl:if test="position() = last()">
        <xsl:call-template name="c-prec-ric" />
    </xsl:if>
</xsl:for-each>
</xsl:variable>

   <xsl:for-each select="..">
        <xsl:for-each select="svg:tref | svg:tspan">
            <xsl:if test="position() = $pos">
                <xsl:call-template name="calcola-lung-prec">
                    <xsl:with-param name="pos-end">
                        <xsl:value-of select="$pos" />
                    </xsl:with-param>
                    <xsl:with-param name="elemento">
                        <xsl:choose>
                            <xsl:when test="name() = 'tspan'">
                                <xsl:text>tspan</xsl:text>
                            </xsl:when>
                            <xsl:otherwise>
                                <xsl:text>tref</xsl:text>
                            </xsl:otherwise>
                        </xsl:choose>
                    </xsl:with-param>
            </xsl:call-template>
            </xsl:if>
        </xsl:for-each>
   </xsl:for-each>
</xsl:template>

<xsl:template name="c-prec-ric">
<!-- restituisce la posizione dell'elemento tspan o tref immediatamente precedente
     all'elemento considerato che contiene l'attributo x -->
<xsl:choose>
    <xsl:when test="@x or (name() = 'textPath')">
        <xsl:value-of select="count(preceding-sibling::svg:tref) + 
                              count(preceding-sibling::svg:tspan) + 1" />
    </xsl:when>

    <xsl:otherwise>
                <xsl:for-each select="preceding-sibling::svg:tref  |
                                      preceding-sibling::svg:tspan | 
                                      preceding-sibling::svg:textPath">
                        <xsl:if test="position() = last()">
                            <xsl:call-template name="c-prec-ric" />
                        </xsl:if>
                </xsl:for-each>
    </xsl:otherwise>
</xsl:choose>
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: normalizza-spazi *********************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="normalizza-spazi">
    <xsl:param name="stringa" />
    
    <!-- rimpiazza i \n (&#10;) e tutti gli spazi che lo seguono.
         Serve per eliminare gli spazi ridondanti nel testo. -->
    

    <!--
        <xsl:value-of select="normalize-space($stringa)" />
    -->
    
    
    <xsl:choose>
        <xsl:when test="contains($stringa,'&#10;')">
            <xsl:variable name="stringa-iniziale">
                <xsl:value-of select="substring-before($stringa,'&#10;')" />
            </xsl:variable>
            <xsl:variable name="stringa-da-sistemare">
                <xsl:value-of select="substring-after($stringa,'&#10;')" />
            </xsl:variable>
            <xsl:variable name="stringa-succ">
                <xsl:call-template name="elimina-spazi-iniziali">
                    <xsl:with-param name="stringa">
                        <xsl:value-of select="$stringa-da-sistemare" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:variable>
            <xsl:variable name="spazio">
                <xsl:choose>
                    <xsl:when test="starts-with($stringa,'&#10;')">            
                        <xsl:text></xsl:text>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:text></xsl:text>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:variable>
            <xsl:call-template name="normalizza-spazi">
                    <xsl:with-param name="stringa">
                        <xsl:value-of select="concat($stringa-iniziale,
                                                     $spazio,$stringa-succ)" />
                    </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
        <xsl:otherwise>
            <xsl:value-of select="concat($stringa,'')" />
        </xsl:otherwise>
    </xsl:choose>
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: elimina-spazi-iniziali ***************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="elimina-spazi-iniziali">
    <xsl:param name="stringa" />
    <!-- elimina tutti gli spazi iniziali della stringa, fino al primo carattere o fino
         a fine stringa -->
    <!-- ricorsiva -->
    <xsl:choose>
        <xsl:when test="$stringa = ''">
            <xsl:value-of select="$stringa" />
        </xsl:when>
        <xsl:when test="starts-with($stringa,' ')">
            <xsl:call-template name="elimina-spazi-iniziali">
                <xsl:with-param name="stringa">
                    <xsl:value-of select="substring-after($stringa,' ')" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
        <xsl:otherwise>
            <xsl:value-of select="$stringa" />
        </xsl:otherwise>
    </xsl:choose>
    
</xsl:template>

</xsl:stylesheet>
