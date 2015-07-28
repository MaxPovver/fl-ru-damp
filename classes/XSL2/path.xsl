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

<!-- CONTIENE ALCUNE FUNZIONI DI GESTIONE STRINGHE -->

<!-- INDEX:
   * template:
        - svg-path
        - traduci-path
        - before-string
        - primo-valore
        - secondo-valore
        - dal-terzo-valore
        - ultimo-valore
        - penultimo-valore
        - somma-valori
   * match:
        - svg:path
-->


<!-- ********************************************************************************* --> 
<!-- ********************************************************************************* -->
<!-- ********************************* ELEMENTO PATH ********************************* -->
<!-- ********************************************************************************* -->
<!-- ********************************************************************************* -->
<xsl:template name="svg-path">
    <xsl:param name="coord-origin"><xsl:text>-1</xsl:text></xsl:param>
    <xsl:param name="coord-size"><xsl:text>-1</xsl:text></xsl:param>
    <xsl:param name="w"><xsl:text>-1</xsl:text></xsl:param>
    <xsl:param name="h"><xsl:text>-1</xsl:text></xsl:param>

<!-- serve per assegnare un id unico al path -->    
<xsl:variable name="shape-id-path">
    <xsl:text>svg-path</xsl:text>
    <xsl:value-of select="count(preceding::*) + count(descendant::*)" />
</xsl:variable>

<!-- in vml non posso avere un elemento path a se stante, lo devo inserire all'interno
      di un elemento shape -->
<v:shape>
    <xsl:attribute name="style">
        <xsl:text>position: absolute;</xsl:text>
        <xsl:text>left: </xsl:text>
            <xsl:call-template name="x-of-svg" />
        <xsl:text>; top: </xsl:text>
            <xsl:call-template name="y-of-svg" />
        <xsl:text>; width: </xsl:text>
            <xsl:choose>
                <xsl:when test="$w = '-1'">
                    <xsl:call-template name="width-of-svg" />
                </xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="$w" />
                </xsl:otherwise>
            </xsl:choose>
        <xsl:text>; height: </xsl:text>
            <xsl:choose>
                <xsl:when test="$h = '-1'">
                    <xsl:call-template name="height-of-svg" />
                </xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="$h" />
                </xsl:otherwise>
            </xsl:choose>
        <xsl:text>; </xsl:text>
        <xsl:text>z-index: -</xsl:text><xsl:value-of select="$n-elementi" />
        <xsl:text>;</xsl:text>
    </xsl:attribute>
    
    <xsl:attribute name="id"><xsl:value-of select="$shape-id-path" />
    </xsl:attribute>
        <xsl:choose>
            <xsl:when test="$coord-origin = '-1'">
                <xsl:for-each select="ancestor::svg:svg">
                    <xsl:if test="position() = last()">
                        <xsl:call-template name="coord-origin-size" />
                    </xsl:if>
                </xsl:for-each>
            </xsl:when>
            <xsl:otherwise>
                <xsl:attribute name="coordorigin">
                    <xsl:value-of select="$coord-origin" />
                </xsl:attribute>
                <xsl:attribute name="coordsize">
                    <xsl:value-of select="$coord-size" />
                </xsl:attribute>
            </xsl:otherwise>
        </xsl:choose>
     
    
    <xsl:call-template name="attributi-paint" /> 
    
<!-- creo l'elemento path -->
<v:path>

    <xsl:call-template name="attributi-core" />
    <xsl:call-template name="attributi-style" />
    <xsl:call-template name="attributi-graphics" />
    <xsl:call-template name="attributi-opacity" />
    <xsl:call-template name="attributi-conditional" />
    <xsl:call-template name="attributi-graphics" />
    <xsl:call-template name="attributi-mask" />
    <xsl:call-template name="attributi-filter" />
    <xsl:call-template name="attributi-clip" />
    <xsl:call-template name="attributi-graphical-event" />
    <xsl:call-template name="attributi-cursor" />
    <xsl:call-template name="attributi-external" />
    
    
    <xsl:attribute name="style">
        <xsl:call-template name="coord-xy" />
    </xsl:attribute>

    
<xsl:if test="@pathLenght">
    <!-- non gestito -->
</xsl:if>


<!-- Considerazioni sulla gestione di alcuni tipi di path: 
1. S: per ogni S, sostituisci S con C, vai indietro di 4 punti e prendine 2, poi bisogna
    modificarli (reflexion):
        >>> 8 casi: (consideriamo il punto finale: x,y e x2,y2 come il punto di controllo
                    da invertire. I nuovi valori sono xn, yn)
                        a. x=x2, y>y2 : dy=y-y2, xn = x, yn = y+dy; 
                        b. x<x2, y>y2 : dy=y-y2, dx=x2-x, xn = x-dx, yn = y+dy;
                        c. x<x2, y=y2 : dx=x2-x, xn = x-dx, yn = y;
                        d. x<x2, y<y2 : dx=x2-x, dy=y2-y, xn = x-dx, yn= y-dy;
                        e. x=x2, y<y2 : dy=y2-y, xn = x, yn = y-dy;
                        f. x>x2, y<y2 : dx=x-x2, dy=y2-y, xn = x+dx, yn = y-dy;
                        g. x>x2, y=y2 : dx=x-x2, xn = x+dx, yn = y;
                        h: x>x2, y>y2 : dx=x-x2, dy=y-y2, xn = x+dx, yn= y+dy;
        >>> se non c'è C,c,S,s prima di S allora: xn=x, yn=y
2. T: per ogni T, sostituisci T con qb, vai indietro di 4 punti e prendine 2, poi bisogna
    modificarli (reflexion):
        >>> 8 casi: (consideriamo il punto finale: x,y e x2,y2 come il punto di controllo
                    da invertire. I nuovi valori sono xn, yn)
                        a. x=x2, y>y2 : dy=y-y2, xn = x, yn = y+dy; 
                        b. x<x2, y>y2 : dy=y-y2, dx=x2-x, xn = x-dx, yn = y+dy;
                        c. x<x2, y=y2 : dx=x2-x, xn = x-dx, yn = y;
                        d. x<x2, y<y2 : dx=x2-x, dy=y2-y, xn = x-dx, yn= y-dy;
                        e. x=x2, y<y2 : dy=y2-y, xn = x, yn = y-dy;
                        f. x>x2, y<y2 : dx=x-x2, dy=y2-y, xn = x+dx, yn = y-dy;
                        g. x>x2, y=y2 : dx=x-x2, xn = x+dx, yn = y;
                        h: x>x2, y>y2 : dx=x-x2, dy=y-y2, xn = x+dx, yn= y+dy;
        >>> se non c'è Q,q,T,t prima di S allora: xn=x, yn=y
3. A: viena approssimato. 
-->

<!-- gestione dell'attributo che rappresenta il path -->
<xsl:attribute name="v">
    <xsl:call-template name="traduci-path">
        <xsl:with-param name="d">
            <xsl:value-of select="@d" />
        </xsl:with-param>
    </xsl:call-template>
</xsl:attribute>

<xsl:call-template name="attributo-title" />

<xsl:apply-templates /> 
</v:path>
</v:shape>

<!-- aggiungo il marker dopo il path -->
<xsl:call-template name="attributi-marker" />

</xsl:template>

<!-- ********************************************************************************* -->
<!-- ************************** TEMPLATE per path ************************************ -->
<xsl:template match="svg:path">

<xsl:variable name="cs">
        <xsl:call-template name="preceding-svg" />
</xsl:variable>

    <!-- gestisco eventuali trasformazioni -->
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
        <xsl:call-template name="svg-path" />
    </xsl:otherwise>
    </xsl:choose>
</xsl:template>


<!-- ********************************************************************************* -->
<!-- ********************************************************************************* -->
<!-- ********************************* TEMPLATE TRADUCI PATH ************************* -->
<!-- ********************************************************************************* -->
<!-- ********************************************************************************* -->
<xsl:template name="traduci-path">
    <xsl:param name="d" />
    
<!-- per ogni comando contenuto in d (nella forma nome-valore valori), traduce il nome 
        nel corrispettivo nome usato da svg ed eventualmente modifica i paramentri, 
        usando template che estraggono porzioni di stringa.
    Nota: essendo una funzione ricorsiva, cambiando alcuni nomi di comandi, si 
          creerebbe un conflitto, cioè si formerebbe un loop infinito, quindi alcuni
          comandi vengono tradotti inizialmente con un carattere che non può essere
          presente in path (per non compromettere ricerche successive) e quando si
          giunge alla fine della gestione del path, si rimettono i nomi corretti. -->
    
<!-- nota sulla traduzione:
    Valori tradotti correttamente:
        - M, m
        - Z, z
        - L, l
        - H, h
        - V, v
        - C, c
    Valori approssimati:
        - S, s
        - Q, q
        - T, t
        - A, a
-->
    
 <xsl:choose>
        <!-- XXXXXXXXXXXX . XXXXXXXXXX -->
        <!-- NB: VML non rappresenta correttamente i path con valori decimali,
                     per ottenere una buona rappresentazione, si tolgono tutte
                     le cifre dopo il punto, in questo modo il path sara' un po'
                     diverso da quello di partenza ma rappresenta tuttavia una buona
                     approssimazione.
        -->
        <xsl:when test="contains($d,'.')">
            <xsl:variable name="dopo-il-valore">
                <xsl:value-of select="substring-after(substring-after($d,'.'),' ')" />
            </xsl:variable>
            
            <xsl:variable name="new-d">
                
                <xsl:value-of select="concat(substring-before($d,'.'),' ',
                                             $dopo-il-valore)" />
            </xsl:variable>
            <xsl:call-template name="traduci-path">
                <xsl:with-param name="d">
                    <xsl:value-of select="$new-d" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
 
 
        <!-- XXXXXXXXXXXX z XXXXXXXXXX -->
        <xsl:when test="contains($d,'z')">
            <xsl:variable name="new-d">
                <xsl:value-of select="concat(substring-before($d,'z'),'x',
                                             substring-after($d,'z'))" />
            </xsl:variable>
            <xsl:call-template name="traduci-path">
                <xsl:with-param name="d">
                    <xsl:value-of select="$new-d" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
        
        <!-- XXXXXXXXXXXX Z XXXXXXXXXX -->
        <xsl:when test="contains($d,'Z')">
            <xsl:variable name="new-d">
                <xsl:value-of select="concat(substring-before($d,'Z'),'x',
                                             substring-after($d,'Z'))" />
            </xsl:variable>
            <xsl:call-template name="traduci-path">
                <xsl:with-param name="d">
                    <xsl:value-of select="$new-d" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
        
        <!-- XXXXXXXXXXXX l XXXXXXXXXX -->
        <xsl:when test="contains($d,'l')">
            <xsl:variable name="new-d">
                <xsl:value-of select="concat(substring-before($d,'l'),'r',
                                             substring-after($d,'l'))" />
            </xsl:variable>
            <xsl:call-template name="traduci-path">
                <xsl:with-param name="d">
                    <xsl:value-of select="$new-d" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
        
        <!-- XXXXXXXXXXXX M XXXXXXXXXX -->       
        <xsl:when test="contains($d,'M')">
            <xsl:variable name="prec">
                <xsl:value-of select="substring-before($d,'M')" />
            </xsl:variable>
            <xsl:variable name="succ-temp"> <!-- tutto quello che segue M -->
                <xsl:value-of select="substring-after($d,'M')" />
            </xsl:variable>
            <xsl:variable name="inside"> <!-- i punti di M -->
                <xsl:call-template name="before-string">
                    <xsl:with-param name="d">
                        <xsl:value-of select="$succ-temp" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:variable>
            <xsl:variable name="succ"> <!-- tutto quello che segue i punti di M -->
                <xsl:value-of select="substring-after($succ-temp,$inside)" />
            </xsl:variable>

            <xsl:variable name="valori-m">
                <xsl:call-template name="primo-valore">
                    <xsl:with-param name="stringa">
                        <xsl:value-of select="normalize-space($inside)" />
                    </xsl:with-param>
                </xsl:call-template>
                <xsl:text> </xsl:text>
                <xsl:call-template name="secondo-valore">
                    <xsl:with-param name="stringa">
                        <xsl:value-of select="normalize-space($inside)" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:variable>
            
            <xsl:variable name="valori-l">
                <xsl:call-template name="dal-terzo-valore">
                    <xsl:with-param name="stringa">
                        <xsl:value-of select="normalize-space($inside)" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:variable>
            <xsl:variable name="is-l">
                <xsl:choose>
                    <xsl:when test="normalize-space($valori-l) != ''">
                        <xsl:value-of select="concat(' L ', $valori-l,' ')" />
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:text></xsl:text>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:variable>
            
            <xsl:variable name="new-d">
                <xsl:value-of select="concat($prec,' # ',$valori-m,$is-l,$succ)" />
            </xsl:variable>
            <xsl:call-template name="traduci-path">
                <xsl:with-param name="d">
                    <xsl:value-of select="$new-d" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
        
        <!-- XXXXXXXXXXXX m XXXXXXXXXX -->
        <xsl:when test="contains($d,'m')">
            <xsl:variable name="prec">
                <xsl:value-of select="substring-before($d,'m')" />
            </xsl:variable>
            <xsl:variable name="succ-temp"> <!-- tutto quello che segue m -->
                <xsl:value-of select="substring-after($d,'m')" />
            </xsl:variable>
            <xsl:variable name="inside"> <!-- i punti di m -->
                <xsl:call-template name="before-string">
                    <xsl:with-param name="d">
                        <xsl:value-of select="$succ-temp" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:variable>
            <xsl:variable name="succ"> <!-- tutto quello che segue i punti di m -->
                <xsl:value-of select="substring-after($succ-temp,$inside)" />
            </xsl:variable>
            
            <xsl:variable name="valori-m">
                <xsl:call-template name="primo-valore">
                    <xsl:with-param name="stringa">
                        <xsl:value-of select="normalize-space($inside)" />
                    </xsl:with-param>
                </xsl:call-template>
                <xsl:text> </xsl:text>
                <xsl:call-template name="secondo-valore">
                    <xsl:with-param name="stringa">
                        <xsl:value-of select="normalize-space($inside)" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:variable>
            
            <xsl:variable name="valori-l">
                <xsl:call-template name="dal-terzo-valore">
                    <xsl:with-param name="stringa">
                        <xsl:value-of select="normalize-space($inside)" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:variable>     
            <xsl:variable name="is-l">
                <xsl:choose>
                    <xsl:when test="normalize-space($valori-l) != ''">
                        <xsl:value-of select="concat(' r ', $valori-l,' ')" />
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:text></xsl:text>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:variable>       
            
            <xsl:variable name="new-d">
                <xsl:value-of select="concat($prec,' ^ ',$valori-m,$is-l,$succ)" />
            </xsl:variable>
            <xsl:call-template name="traduci-path">
                <xsl:with-param name="d">
                    <xsl:value-of select="$new-d" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
        
        <!-- XXXXXXXXXXXX H XXXXXXXXXX -->       
        <xsl:when test="contains($d,'H')">
            <xsl:variable name="prec">
                <xsl:value-of select="substring-before($d,'H')" />
            </xsl:variable>
            <xsl:variable name="succ-temp"> <!-- tutto quello che segue H -->
                <xsl:value-of select="substring-after($d,'H')" />
            </xsl:variable>
            <xsl:variable name="inside"> <!-- i punti di H -->
                <xsl:call-template name="before-string">
                    <xsl:with-param name="d">
                        <xsl:value-of select="$succ-temp" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:variable>
            <xsl:variable name="succ"> <!-- tutto quello che segue i punti di H -->
                <xsl:value-of select="substring-after($succ-temp,$inside)" />
            </xsl:variable>

            <xsl:variable name="valore-h">
                <xsl:call-template name="ultimo-valore">
                    <xsl:with-param name="stringa">
                        <xsl:value-of select="normalize-space($inside)" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:variable>
            
            <xsl:variable name="valore-y">
                <xsl:call-template name="ultimo-valore">
                    <xsl:with-param name="stringa">
                        <xsl:value-of select="normalize-space($prec)" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:variable>
            
            <xsl:variable name="new-d">
                <xsl:value-of select="concat($prec,' L ',$valore-h,' ',
                                      $valore-y,' ',$succ)" />
            </xsl:variable>
            <xsl:call-template name="traduci-path">
                <xsl:with-param name="d">
                    <xsl:value-of select="$new-d" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
        
        <!-- XXXXXXXXXXXX h XXXXXXXXXX -->       
        <xsl:when test="contains($d,'h')">
            <xsl:variable name="prec">
                <xsl:value-of select="substring-before($d,'h')" />
            </xsl:variable>
            <xsl:variable name="succ-temp"> <!-- tutto quello che segue h -->
                <xsl:value-of select="substring-after($d,'h')" />
            </xsl:variable>
            <xsl:variable name="inside"> <!-- i punti di h -->
                <xsl:call-template name="before-string">
                    <xsl:with-param name="d">
                        <xsl:value-of select="$succ-temp" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:variable>
            <xsl:variable name="succ"> <!-- tutto quello che segue i punti di h -->
                <xsl:value-of select="substring-after($succ-temp,$inside)" />
            </xsl:variable>

            <xsl:variable name="valore-h">
                <xsl:call-template name="somma-valori">
                    <xsl:with-param name="stringa">
                        <xsl:value-of select="normalize-space($inside)" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:variable>
            
            <xsl:variable name="new-d">
                <xsl:value-of select="concat($prec,' r ',$valore-h,' 0 ',$succ)" />
            </xsl:variable>
            <xsl:call-template name="traduci-path">
                <xsl:with-param name="d">
                    <xsl:value-of select="$new-d" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
        
        <!-- XXXXXXXXXXXX V XXXXXXXXXX -->       
        <xsl:when test="contains($d,'V')">
            <xsl:variable name="prec">
                <xsl:value-of select="substring-before($d,'V')" />
            </xsl:variable>
            <xsl:variable name="succ-temp"> <!-- tutto quello che segue V -->
                <xsl:value-of select="substring-after($d,'V')" />
            </xsl:variable>
            <xsl:variable name="inside"> <!-- i punti di V -->
                <xsl:call-template name="before-string">
                    <xsl:with-param name="d">
                        <xsl:value-of select="$succ-temp" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:variable>
            <xsl:variable name="succ"> <!-- tutto quello che segue i punti di V -->
                <xsl:value-of select="substring-after($succ-temp,$inside)" />
            </xsl:variable>

            <xsl:variable name="valore-v">
                <xsl:call-template name="ultimo-valore">
                    <xsl:with-param name="stringa">
                        <xsl:value-of select="normalize-space($inside)" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:variable>
            
            <xsl:variable name="valore-x">
                <xsl:call-template name="penultimo-valore">
                    <xsl:with-param name="stringa">
                        <xsl:value-of select="normalize-space($prec)" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:variable>
            
            <xsl:variable name="new-d">
                <xsl:value-of select="concat($prec,' L ',$valore-x,' ',
                                      $valore-v,' ',$succ)" />
            </xsl:variable>
            <xsl:call-template name="traduci-path">
                <xsl:with-param name="d">
                    <xsl:value-of select="$new-d" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
        
        <!-- XXXXXXXXXXXX v XXXXXXXXXX -->       
        <xsl:when test="contains($d,'v')">
            <xsl:variable name="prec">
                <xsl:value-of select="substring-before($d,'v')" />
            </xsl:variable>
            <xsl:variable name="succ-temp"> <!-- tutto quello che segue v -->
                <xsl:value-of select="substring-after($d,'v')" />
            </xsl:variable>
            <xsl:variable name="inside"> <!-- i punti di v -->
                <xsl:call-template name="before-string">
                    <xsl:with-param name="d">
                        <xsl:value-of select="$succ-temp" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:variable>
            <xsl:variable name="succ"> <!-- tutto quello che segue i punti di v -->
                <xsl:value-of select="substring-after($succ-temp,$inside)" />
            </xsl:variable>

            <xsl:variable name="valore-v">
                <xsl:call-template name="somma-valori">
                    <xsl:with-param name="stringa">
                        <xsl:value-of select="normalize-space($inside)" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:variable>
            
            <xsl:variable name="new-d">
                <xsl:value-of select="concat($prec,' r 0 ',$valore-v,' ',$succ)" />
            </xsl:variable>
            <xsl:call-template name="traduci-path">
                <xsl:with-param name="d">
                    <xsl:value-of select="$new-d" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
        
        <!-- XXXXXXXXXXXX c XXXXXXXXXX -->
        <xsl:when test="contains($d,'c')">
            <xsl:variable name="new-d">
                <xsl:value-of select="concat(substring-before($d,'c'),'@',
                                             substring-after($d,'c'))" />
            </xsl:variable>
            <xsl:call-template name="traduci-path">
                <xsl:with-param name="d">
                    <xsl:value-of select="$new-d" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
        
        <!-- XXXXXXXXXXXX S XXXXXXXXXX -->
        <xsl:when test="contains($d,'S')">
            <xsl:variable name="prec">
                <xsl:value-of select="substring-before($d,'S')" />
            </xsl:variable>
            <xsl:variable name="succ-temp"> <!-- tutto quello che segue S -->
                <xsl:value-of select="substring-after($d,'S')" />
            </xsl:variable>
            <xsl:variable name="inside"> <!-- i punti di S -->
                <xsl:call-template name="before-string">
                    <xsl:with-param name="d">
                        <xsl:value-of select="$succ-temp" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:variable>
            <xsl:variable name="succ"> <!-- tutto quello che segue i punti di S -->
                <xsl:value-of select="substring-after($succ-temp,$inside)" />
            </xsl:variable>
            
            <xsl:variable name="current-point">
                <xsl:call-template name="penultimo-valore">
                    <xsl:with-param name="stringa">
                        <xsl:value-of select="normalize-space($prec)" />
                    </xsl:with-param>
                </xsl:call-template>
                <xsl:text> </xsl:text>
                <xsl:call-template name="ultimo-valore">
                    <xsl:with-param name="stringa">
                        <xsl:value-of select="normalize-space($prec)" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:variable>

            <xsl:variable name="new-d">
                <xsl:value-of select="concat($prec,' C ',$current-point,' ',
                                             $inside,' ',$succ)" />
            </xsl:variable>
            <xsl:call-template name="traduci-path">
                <xsl:with-param name="d">
                    <xsl:value-of select="$new-d" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
        
        <!-- XXXXXXXXXXXX s XXXXXXXXXX -->
        <xsl:when test="contains($d,'s')">
            <xsl:variable name="prec">
                <xsl:value-of select="substring-before($d,'s')" />
            </xsl:variable>
            <xsl:variable name="succ-temp"> <!-- tutto quello che segue s -->
                <xsl:value-of select="substring-after($d,'s')" />
            </xsl:variable>
            <xsl:variable name="inside"> <!-- i punti di s -->
                <xsl:call-template name="before-string">
                    <xsl:with-param name="d">
                        <xsl:value-of select="$succ-temp" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:variable>
            <xsl:variable name="succ"> <!-- tutto quello che segue i punti di s -->
                <xsl:value-of select="substring-after($succ-temp,$inside)" />
            </xsl:variable>
            
            <xsl:variable name="current-point">
                <xsl:call-template name="penultimo-valore">
                    <xsl:with-param name="stringa">
                        <xsl:value-of select="normalize-space($prec)" />
                    </xsl:with-param>
                </xsl:call-template>
                <xsl:text> </xsl:text>
                <xsl:call-template name="ultimo-valore">
                    <xsl:with-param name="stringa">
                        <xsl:value-of select="normalize-space($prec)" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:variable>

            <xsl:variable name="new-d">
                <xsl:value-of select="concat($prec,' @ ',$current-point,' ',
                                             $inside,' ',$succ)" />
            </xsl:variable>
            <xsl:call-template name="traduci-path">
                <xsl:with-param name="d">
                    <xsl:value-of select="$new-d" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>      
        
        <!-- XXXXXXXXXXXX Q XXXXXXXXXX -->
        <xsl:when test="contains($d,'Q')">
            <xsl:variable name="prec">
                <xsl:value-of select="substring-before($d,'Q')" />
            </xsl:variable>
            <xsl:variable name="succ-temp"> <!-- tutto quello che segue Q -->
                <xsl:value-of select="substring-after($d,'Q')" />
            </xsl:variable>
            
            <xsl:variable name="inside"> <!-- i punti di Q -->
                <xsl:call-template name="before-string">
                    <xsl:with-param name="d">
                        <xsl:value-of select="$succ-temp" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:variable>
            <xsl:variable name="succ"> <!-- tutto quello che segue i punti di Q -->
                <xsl:value-of select="substring-after($succ-temp,$inside)" />
            </xsl:variable>
        
            <xsl:variable name="new-d">
                <xsl:value-of select="concat($prec,'£| ',$inside,
                                            ' r 0,0 ', $succ)" />
            </xsl:variable>
            <xsl:call-template name="traduci-path">
                <xsl:with-param name="d">
                    <xsl:value-of select="$new-d" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
 

        <!-- XXXXXXXXXXXX q XXXXXXXXXX -->
        <xsl:when test="contains($d,'q')">
            <xsl:variable name="prec">
                <xsl:value-of select="substring-before($d,'q')" />
            </xsl:variable>
            <xsl:variable name="succ-temp"> <!-- tutto quello che segue q -->
                <xsl:value-of select="substring-after($d,'q')" />
            </xsl:variable>
            <xsl:variable name="inside"> <!-- i punti di q -->
                <xsl:call-template name="before-string">
                    <xsl:with-param name="d">
                        <xsl:value-of select="$succ-temp" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:variable>
            <xsl:variable name="succ"> <!-- tutto quello che segue i punti di q -->
                <xsl:value-of select="substring-after($succ-temp,$inside)" />
            </xsl:variable>
            
            <xsl:variable name="valore-r">
                <xsl:call-template name="penultimo-valore">
                    <xsl:with-param name="stringa">
                        <xsl:value-of select="normalize-space($inside)" />
                    </xsl:with-param>
                </xsl:call-template>
                <xsl:text> </xsl:text>
                <xsl:call-template name="ultimo-valore">
                    <xsl:with-param name="stringa">
                        <xsl:value-of select="normalize-space($inside)" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:variable>

            <xsl:variable name="new-d">
                <xsl:value-of select="concat($prec,' r ',$valore-r,' ',$succ)" />
            </xsl:variable>
            <xsl:call-template name="traduci-path">
                <xsl:with-param name="d">
                    <xsl:value-of select="$new-d" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when> 

        
        <!-- XXXXXXXXXXXX T XXXXXXXXXX -->
        <xsl:when test="contains($d,'T')">
            <xsl:variable name="new-d">
                <xsl:value-of select="concat(substring-before($d,'T'),'L',
                                             substring-after($d,'T'))" />
            </xsl:variable>
            <xsl:call-template name="traduci-path">
                <xsl:with-param name="d">
                    <xsl:value-of select="$new-d" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
        
        <!-- XXXXXXXXXXXX t XXXXXXXXXX -->
        <xsl:when test="contains($d,'t')">
            <xsl:variable name="prec">
                <xsl:value-of select="substring-before($d,'t')" />
            </xsl:variable>
            <xsl:variable name="succ-temp"> <!-- tutto quello che segue t -->
                <xsl:value-of select="substring-after($d,'t')" />
            </xsl:variable>
            <xsl:variable name="inside"> <!-- i punti di t -->
                <xsl:call-template name="before-string">
                    <xsl:with-param name="d">
                        <xsl:value-of select="$succ-temp" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:variable>
            <xsl:variable name="succ"> <!-- tutto quello che segue i punti di t -->
                <xsl:value-of select="substring-after($succ-temp,$inside)" />
            </xsl:variable>
            <xsl:variable name="new-d">
                <xsl:value-of select="concat($prec,' r ',$inside,' ',$succ)" />
            </xsl:variable>
            <xsl:call-template name="traduci-path">
                <xsl:with-param name="d">
                    <xsl:value-of select="$new-d" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when> 
        
        <!-- XXXXXXXXXXXX A XXXXXXXXXX -->       
        <xsl:when test="contains($d,'A')">
            <xsl:variable name="prec">
                <xsl:value-of select="substring-before($d,'A')" />
            </xsl:variable>
            <xsl:variable name="succ-temp"> <!-- tutto quello che segue A -->
                <xsl:value-of select="substring-after($d,'A')" />
            </xsl:variable>
            <xsl:variable name="inside"> <!-- i punti di A -->
                <xsl:call-template name="before-string">
                    <xsl:with-param name="d">
                        <xsl:value-of select="$succ-temp" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:variable>
            <xsl:variable name="succ"> <!-- tutto quello che segue i punti di A -->
                <xsl:value-of select="substring-after($succ-temp,$inside)" />
            </xsl:variable>
            
            <xsl:variable name="valore-l">
                <xsl:call-template name="penultimo-valore">
                    <xsl:with-param name="stringa">
                        <xsl:value-of select="normalize-space($inside)" />
                    </xsl:with-param>
                </xsl:call-template>
                <xsl:text> </xsl:text>
                <xsl:call-template name="ultimo-valore">
                    <xsl:with-param name="stringa">
                        <xsl:value-of select="normalize-space($inside)" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:variable>
            
            <xsl:variable name="new-d">
                <xsl:value-of select="concat($prec,' L ',$valore-l,' ',$succ)" />
            </xsl:variable>
            <xsl:call-template name="traduci-path">
                <xsl:with-param name="d">
                    <xsl:value-of select="$new-d" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
        
        <!-- XXXXXXXXXXXX a XXXXXXXXXX -->       
        <xsl:when test="contains($d,'a')">
            <xsl:variable name="prec">
                <xsl:value-of select="substring-before($d,'a')" />
            </xsl:variable>
            <xsl:variable name="succ-temp"> <!-- tutto quello che segue a -->
                <xsl:value-of select="substring-after($d,'a')" />
            </xsl:variable>
            <xsl:variable name="inside"> <!-- i punti di a -->
                <xsl:call-template name="before-string">
                    <xsl:with-param name="d">
                        <xsl:value-of select="$succ-temp" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:variable>
            <xsl:variable name="succ"> <!-- tutto quello che segue i punti di a -->
                <xsl:value-of select="substring-after($succ-temp,$inside)" />
            </xsl:variable>
            
            <xsl:variable name="valore-r">
                <xsl:call-template name="penultimo-valore">
                    <xsl:with-param name="stringa">
                        <xsl:value-of select="normalize-space($inside)" />
                    </xsl:with-param>
                </xsl:call-template>
                <xsl:text> </xsl:text>
                <xsl:call-template name="ultimo-valore">
                    <xsl:with-param name="stringa">
                        <xsl:value-of select="normalize-space($inside)" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:variable>
            
            <xsl:variable name="new-d">
                <xsl:value-of select="concat($prec,' r ',$valore-r,' ',$succ)" />
            </xsl:variable>
            <xsl:call-template name="traduci-path">
                <xsl:with-param name="d">
                    <xsl:value-of select="$new-d" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
 
 
        <!-- XXXXXXXXXXXXX OTHERWISE XXXXXXXXXXXX -->
        <xsl:otherwise>
            <xsl:value-of select="concat(translate(translate(
            translate(translate($d,'#','m'),'@','v'),'^','t'),'£|','qb'),' e')" />
        </xsl:otherwise>
</xsl:choose>   
</xsl:template>

<!-- ********************************************************************************* -->
<!-- ********************************************************************************* -->
<!-- ************************ TEMPLATE: BEFORE STRING********************************* -->
<!-- ********************************************************************************* -->
<!-- ********************************************************************************* -->
<xsl:template name="before-string">
    <xsl:param name="d" />
    <!-- restituisce la parte di d che precedente un qualsiasi comando di path -->
    <!-- cioè solo dei punti -->
    
    <!-- quando devo analizzare i valori di un dato comando, estraggo la sottostringa,
          dell'attributo che rappresenta il path, dal comando (dopo il nome) che sto 
          analizzando fino alla fine della stringa. A me però interessano solo i punti, 
          per ottenerli, utilizzo questa funzione che elimina tutto quello che segue 
          un qualsiasi comando (e il nome del comando stesso), ed essendo ricorsiva,
          eliminerà tutti i comandi successivi, lasciando solo i valori iniziali (che
          rappresentano i valori del comando che sto analizzando).
    -->
    
    <xsl:choose>
        <xsl:when test="contains($d,'#')">
            <xsl:variable name="d-temp">
                <xsl:value-of select="substring-before($d,'#')" />
            </xsl:variable>
            <xsl:call-template name="before-string">
                <xsl:with-param name="d">
                    <xsl:value-of select="$d-temp" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
        <xsl:when test="contains($d,'@')">
            <xsl:variable name="d-temp">
                <xsl:value-of select="substring-before($d,'@')" />
            </xsl:variable>
            <xsl:call-template name="before-string">
                <xsl:with-param name="d">
                    <xsl:value-of select="$d-temp" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
                <xsl:when test="contains($d,'^')">
            <xsl:variable name="d-temp">
                <xsl:value-of select="substring-before($d,'^')" />
            </xsl:variable>
            <xsl:call-template name="before-string">
                <xsl:with-param name="d">
                    <xsl:value-of select="$d-temp" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
        
        <xsl:when test="contains($d,'£|')">
            <xsl:variable name="d-temp">
                <xsl:value-of select="substring-before($d,'£|')" />
            </xsl:variable>
            <xsl:call-template name="before-string">
                <xsl:with-param name="d">
                    <xsl:value-of select="$d-temp" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
    
        <xsl:when test="contains($d,'r')">
            <xsl:variable name="d-temp">
                <xsl:value-of select="substring-before($d,'r')" />
            </xsl:variable>
            <xsl:call-template name="before-string">
                <xsl:with-param name="d">
                    <xsl:value-of select="$d-temp" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
        <xsl:when test="contains($d,'x')">
            <xsl:variable name="d-temp">
                <xsl:value-of select="substring-before($d,'x')" />
            </xsl:variable>
            <xsl:call-template name="before-string">
                <xsl:with-param name="d">
                    <xsl:value-of select="$d-temp" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
        <xsl:when test="contains($d,'e')">
            <xsl:variable name="d-temp">
                <xsl:value-of select="substring-before($d,'e')" />
            </xsl:variable>
            <xsl:call-template name="before-string">
                <xsl:with-param name="d">
                    <xsl:value-of select="$d-temp" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
        <xsl:when test="contains($d,'n')">
            <xsl:variable name="d-temp">
                <xsl:value-of select="substring-before($d,'n')" />
            </xsl:variable>
            <xsl:call-template name="before-string">
                <xsl:with-param name="d">
                    <xsl:value-of select="$d-temp" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
        <xsl:when test="contains($d,'w')">
            <xsl:variable name="d-temp">
                <xsl:value-of select="substring-before($d,'w')" />
            </xsl:variable>
            <xsl:call-template name="before-string">
                <xsl:with-param name="d">
                    <xsl:value-of select="$d-temp" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
        
        <xsl:when test="contains($d,'m')">
            <xsl:variable name="d-temp">
                <xsl:value-of select="substring-before($d,'m')" />
            </xsl:variable>
            <xsl:call-template name="before-string">
                <xsl:with-param name="d">
                    <xsl:value-of select="$d-temp" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
        <xsl:when test="contains($d,'M')">
            <xsl:variable name="d-temp">
                <xsl:value-of select="substring-before($d,'M')" />
            </xsl:variable>
            <xsl:call-template name="before-string">
                <xsl:with-param name="d">
                    <xsl:value-of select="$d-temp" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
        <xsl:when test="contains($d,'z')">
            <xsl:variable name="d-temp">
                <xsl:value-of select="substring-before($d,'z')" />
            </xsl:variable>
            <xsl:call-template name="before-string">
                <xsl:with-param name="d">
                    <xsl:value-of select="$d-temp" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
        <xsl:when test="contains($d,'Z')">
            <xsl:variable name="d-temp">
                <xsl:value-of select="substring-before($d,'Z')" />
            </xsl:variable>
            <xsl:call-template name="before-string">
                <xsl:with-param name="d">
                    <xsl:value-of select="$d-temp" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
        <xsl:when test="contains($d,'l')">
            <xsl:variable name="d-temp">
                <xsl:value-of select="substring-before($d,'l')" />
            </xsl:variable>
            <xsl:call-template name="before-string">
                <xsl:with-param name="d">
                    <xsl:value-of select="$d-temp" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
        <xsl:when test="contains($d,'L')">
            <xsl:variable name="d-temp">
                <xsl:value-of select="substring-before($d,'L')" />
            </xsl:variable>
            <xsl:call-template name="before-string">
                <xsl:with-param name="d">
                    <xsl:value-of select="$d-temp" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
        <xsl:when test="contains($d,'h')">
            <xsl:variable name="d-temp">
                <xsl:value-of select="substring-before($d,'h')" />
            </xsl:variable>
            <xsl:call-template name="before-string">
                <xsl:with-param name="d">
                    <xsl:value-of select="$d-temp" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
        <xsl:when test="contains($d,'H')">
            <xsl:variable name="d-temp">
                <xsl:value-of select="substring-before($d,'H')" />
            </xsl:variable>
            <xsl:call-template name="before-string">
                <xsl:with-param name="d">
                    <xsl:value-of select="$d-temp" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
        <xsl:when test="contains($d,'v')">
            <xsl:variable name="d-temp">
                <xsl:value-of select="substring-before($d,'v')" />
            </xsl:variable>
            <xsl:call-template name="before-string">
                <xsl:with-param name="d">
                    <xsl:value-of select="$d-temp" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
        <xsl:when test="contains($d,'V')">
            <xsl:variable name="d-temp">
                <xsl:value-of select="substring-before($d,'V')" />
            </xsl:variable>
            <xsl:call-template name="before-string">
                <xsl:with-param name="d">
                    <xsl:value-of select="$d-temp" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
        <xsl:when test="contains($d,'c')">
            <xsl:variable name="d-temp">
                <xsl:value-of select="substring-before($d,'c')" />
            </xsl:variable>
            <xsl:call-template name="before-string">
                <xsl:with-param name="d">
                    <xsl:value-of select="$d-temp" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
        <xsl:when test="contains($d,'C')">
            <xsl:variable name="d-temp">
                <xsl:value-of select="substring-before($d,'C')" />
            </xsl:variable>
            <xsl:call-template name="before-string">
                <xsl:with-param name="d">
                    <xsl:value-of select="$d-temp" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
        <xsl:when test="contains($d,'s')">
            <xsl:variable name="d-temp">
                <xsl:value-of select="substring-before($d,'s')" />
            </xsl:variable>
            <xsl:call-template name="before-string">
                <xsl:with-param name="d">
                    <xsl:value-of select="$d-temp" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
        <xsl:when test="contains($d,'S')">
            <xsl:variable name="d-temp">
                <xsl:value-of select="substring-before($d,'S')" />
            </xsl:variable>
            <xsl:call-template name="before-string">
                <xsl:with-param name="d">
                    <xsl:value-of select="$d-temp" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
        <xsl:when test="contains($d,'q')">
            <xsl:variable name="d-temp">
                <xsl:value-of select="substring-before($d,'q')" />
            </xsl:variable>
            <xsl:call-template name="before-string">
                <xsl:with-param name="d">
                    <xsl:value-of select="$d-temp" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when> 
        <xsl:when test="contains($d,'Q')">
            <xsl:variable name="d-temp">
                <xsl:value-of select="substring-before($d,'Q')" />
            </xsl:variable>
            <xsl:call-template name="before-string">
                <xsl:with-param name="d">
                    <xsl:value-of select="$d-temp" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when> 
        <xsl:when test="contains($d,'t')">
            <xsl:variable name="d-temp">
                <xsl:value-of select="substring-before($d,'t')" />
            </xsl:variable>
            <xsl:call-template name="before-string">
                <xsl:with-param name="d">
                    <xsl:value-of select="$d-temp" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when> 
        <xsl:when test="contains($d,'T')">
            <xsl:variable name="d-temp">
                <xsl:value-of select="substring-before($d,'T')" />
            </xsl:variable>
            <xsl:call-template name="before-string">
                <xsl:with-param name="d">
                    <xsl:value-of select="$d-temp" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when> 
        <xsl:when test="contains($d,'a')">
            <xsl:variable name="d-temp">
                <xsl:value-of select="substring-before($d,'a')" />
            </xsl:variable>
            <xsl:call-template name="before-string">
                <xsl:with-param name="d">
                    <xsl:value-of select="$d-temp" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when> 
        <xsl:when test="contains($d,'A')">
            <xsl:variable name="d-temp">
                <xsl:value-of select="substring-before($d,'A')" />
            </xsl:variable>
            <xsl:call-template name="before-string">
                <xsl:with-param name="d">
                    <xsl:value-of select="$d-temp" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when> 
        <xsl:otherwise>
            <xsl:value-of select="$d" />
        </xsl:otherwise>
    </xsl:choose>
</xsl:template>

<!-- ******************************************************************************** -->
<!-- *********************** TEMPLATE per manipolare stringhe *********************** -->
<!-- ******************************************************************************** -->

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: PRIMO VALORE  ************************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="primo-valore">
    <xsl:param name="stringa" />
    <xsl:param name="default"><xsl:text></xsl:text></xsl:param>
    <!-- restituisce il primo valore presente in stringa (i valori sono separati o
            da virgole o da spazi) -->
    <xsl:choose>
        <xsl:when test="contains($stringa,',') and
                        contains(normalize-space($stringa),' ')"> 
                <xsl:choose>
                    <!-- x1,x2 ... xn -->
                    <xsl:when test="contains(substring-before(
                                             normalize-space($stringa),' '),',')">
                        <xsl:value-of select="normalize-space(
                                              substring-before($stringa,','))" />
                    </xsl:when>
                    <!-- x1 x2 ... ,xn -->
                    <xsl:otherwise>
                        <xsl:value-of select="normalize-space(substring-before(
                                              normalize-space($stringa),' '))" />
                    </xsl:otherwise>
                </xsl:choose>
        </xsl:when> 
        <xsl:when test="contains($stringa,',')"> <!-- x1,x2 -->
            <xsl:value-of select="normalize-space(substring-before($stringa,','))" />
        </xsl:when>
        <xsl:when test="contains(normalize-space($stringa),' ')"> <!-- x1 x2 -->
            <xsl:value-of select="normalize-space(substring-before(
                                  normalize-space($stringa),' '))" />
        </xsl:when>    
        <xsl:when test="normalize-space($stringa) != ''">
            <xsl:value-of select="normalize-space($stringa)" />
        </xsl:when> 
        <xsl:otherwise>
            <xsl:value-of select="$default" />
        </xsl:otherwise>        
    </xsl:choose>
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: SECONDO VALORE  ************************************ -->
<!-- ******************************************************************************** -->
<xsl:template name="secondo-valore">
    <xsl:param name="stringa" />
    <xsl:param name="default"><xsl:text></xsl:text></xsl:param>
    <!-- restituisce il secondo valore presente in stringa (stringa può contenere molti 
         valori, anche 0) -->
    <xsl:variable name="stringa-temp">
        <xsl:choose>
            <xsl:when test="contains($stringa,',') and 
                            contains(normalize-space($stringa),' ')">
                <xsl:choose>
                    <!-- x1,x2 ... xn -->
                    <xsl:when test="contains(substring-before(
                                             normalize-space($stringa),' '),',')">
                        <xsl:value-of select="normalize-space(
                                              substring-after($stringa,','))" />
                    </xsl:when>
                    <!-- x1 x2 ... ,xn -->
                    <xsl:otherwise>
                        <xsl:value-of select="normalize-space(substring-after(
                                              normalize-space($stringa),' '))" />
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:when>
            <xsl:when test="contains($stringa,',')"> <!-- x1,x2 ... -->
                <xsl:value-of select="normalize-space(substring-after($stringa,','))" />
            </xsl:when> 
            <xsl:when test="contains(normalize-space($stringa),' ')"><!-- x1 x2 ... -->
                <xsl:value-of select="normalize-space(substring-after(
                                      normalize-space($stringa),' '))" />
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="$default" />
            </xsl:otherwise>   
        </xsl:choose>
    </xsl:variable>
    <xsl:choose>
        <xsl:when test="contains($stringa-temp,',') and 
                        contains(normalize-space($stringa-temp),' ')">
            <xsl:choose>
                <!-- x2,x3 ... xn -->
                <xsl:when test="contains(substring-before(
                                normalize-space($stringa-temp),' '),',')">
                    <xsl:value-of select="normalize-space(
                                          substring-before($stringa-temp,','))" />
                </xsl:when>
                <!-- x2 x3 ... ,xn -->
                <xsl:otherwise>
                    <xsl:value-of select="normalize-space(substring-before(
                                          normalize-space($stringa-temp),' '))" />
                </xsl:otherwise>
            </xsl:choose>
        </xsl:when>
        <xsl:when test="contains($stringa-temp,',')">
            <xsl:value-of select="substring-before($stringa-temp,',')" />
        </xsl:when>
        <xsl:when test="contains($stringa-temp,' ')">
            <xsl:value-of select="normalize-space(
                                  substring-before($stringa-temp,' '))" />        
        </xsl:when>
        <xsl:otherwise>
            <xsl:value-of select="$stringa-temp" />
        </xsl:otherwise>        
    </xsl:choose>
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: DAL TERZO VALORE  ********************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="dal-terzo-valore">
    <xsl:param name="stringa" />
    <xsl:param name="default"><xsl:text></xsl:text></xsl:param>
    <!-- restituisce i valori dal terzo presenti in stringa (stringa può contenere molti 
         valori, anche 0) -->
    <xsl:variable name="stringa-temp">
        <xsl:choose>
            <xsl:when test="contains($stringa,',') and 
                            contains(normalize-space($stringa),' ')">
                <xsl:choose>
                    <!-- x1,x2 ... xn -->
                    <xsl:when test="contains(substring-before(
                                             normalize-space($stringa),' '),',')">
                        <xsl:value-of select="normalize-space(
                                              substring-after($stringa,','))" />
                    </xsl:when>
                    <!-- x1 x2 ... ,xn -->
                    <xsl:otherwise>
                        <xsl:value-of select="normalize-space(substring-after(
                                              normalize-space($stringa),' '))" />
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:when>
            <xsl:when test="contains($stringa,',')"> <!-- x1,x2 ... -->
                <xsl:value-of select="normalize-space(substring-after($stringa,','))" />
            </xsl:when> 
            <xsl:when test="contains(normalize-space($stringa),' ')"><!-- x1 x2 ... -->
                <xsl:value-of select="normalize-space(substring-after(
                                      normalize-space($stringa),' '))" />
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="$default" />
            </xsl:otherwise>   
        </xsl:choose>
    </xsl:variable>
    
    <xsl:choose>
        <xsl:when test="contains($stringa-temp,',') and 
                        contains(normalize-space($stringa-temp),' ')">
            <xsl:choose>
                <!-- x2,x3 ... xn -->
                <xsl:when test="contains(substring-before(
                                normalize-space($stringa-temp),' '),',')">
                    <xsl:value-of select="normalize-space(
                                          substring-after($stringa-temp,','))" />
                </xsl:when>
                <!-- x2 x3 ... ,xn -->
                <xsl:otherwise>
                    <xsl:value-of select="normalize-space(substring-after(
                                          normalize-space($stringa-temp),' '))" />
                </xsl:otherwise>
            </xsl:choose>
        </xsl:when>
        <xsl:when test="contains($stringa-temp,',')">
            <xsl:value-of select="substring-after($stringa-temp,',')" />
        </xsl:when>
        <xsl:when test="contains($stringa-temp,' ')">
            <xsl:value-of select="normalize-space(
                                  substring-after($stringa-temp,' '))" />        
        </xsl:when>
        <xsl:otherwise>
        </xsl:otherwise>        
    </xsl:choose>
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: ULTIMO VALORE  ************************************* -->
<!-- ******************************************************************************** -->
<xsl:template name="ultimo-valore">
    <xsl:param name="stringa" />
    <xsl:param name="default"><xsl:text></xsl:text></xsl:param>
    <!-- restituisce l'ultimo valore presente in stringa (stringa può contenere molti 
         valori, anche 0) -->
    <!-- funzione ricorsiva -->
    <xsl:variable name="stringa-temp">
        <xsl:choose>
            <xsl:when test="contains($stringa,',') and 
                            contains(normalize-space($stringa),' ')">
                <xsl:choose>
                    <!-- x1,x2 ... xn -->
                    <xsl:when test="contains(substring-before(
                                             normalize-space($stringa),' '),',')">
                        <xsl:value-of select="normalize-space(
                                              substring-after($stringa,','))" />
                    </xsl:when>
                    <!-- x1 x2 ... ,xn -->
                    <xsl:otherwise>
                        <xsl:value-of select="normalize-space(substring-after(
                                              normalize-space($stringa),' '))" />
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:when>
            <xsl:when test="contains($stringa,',')"> <!-- x1,x2 ... -->
                <xsl:value-of select="normalize-space(substring-after($stringa,','))" />
            </xsl:when> 
            <xsl:when test="contains(normalize-space($stringa),' ')"><!-- x1 x2 ... -->
                <xsl:value-of select="normalize-space(substring-after(
                                      normalize-space($stringa),' '))" />
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="normalize-space($stringa)" />
            </xsl:otherwise>   
        </xsl:choose>
    </xsl:variable>
    
    <xsl:choose>
        <xsl:when test="contains($stringa-temp,',') or
                        contains(normalize-space($stringa-temp),' ')">
            <xsl:call-template name="ultimo-valore">
                <xsl:with-param name="stringa">
                    <xsl:value-of select="normalize-space($stringa-temp)" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
        <xsl:otherwise>
            <xsl:value-of select="$stringa-temp" />
        </xsl:otherwise>        
    </xsl:choose>
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: PENULTIMO VALORE  ********************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="penultimo-valore">
    <xsl:param name="stringa" />
    <xsl:param name="default"><xsl:text></xsl:text></xsl:param>
    <!-- restituisce il penultimo valore presente in stringa (stringa può contenere molti 
         valori, anche 0) -->
    <!-- funzione ricorsiva -->
    
    <xsl:variable name="stringa-after">
        <xsl:choose>
            <xsl:when test="contains($stringa,',') and 
                            contains(normalize-space($stringa),' ')">
                <xsl:choose>
                    <!-- x1,x2 ... xn -->
                    <xsl:when test="contains(substring-before(
                                             normalize-space($stringa),' '),',')">
                        <xsl:value-of select="normalize-space(
                                              substring-after($stringa,','))" />
                    </xsl:when>
                    <!-- x1 x2 ... ,xn -->
                    <xsl:otherwise>
                        <xsl:value-of select="normalize-space(substring-after(
                                              normalize-space($stringa),' '))" />
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:when>
            <xsl:when test="contains($stringa,',')"> <!-- x1,x2 ... -->
                <xsl:value-of select="normalize-space(substring-after($stringa,','))" />
            </xsl:when> 
            <xsl:when test="contains(normalize-space($stringa),' ')"><!-- x1 x2 ... -->
                <xsl:value-of select="normalize-space(substring-after(
                                      normalize-space($stringa),' '))" />
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="$default" />
            </xsl:otherwise>   
        </xsl:choose>
    </xsl:variable>
    
    <xsl:variable name="stringa-before">
        <xsl:choose>
            <xsl:when test="contains($stringa,',') and 
                            contains(normalize-space($stringa),' ')">
                <xsl:choose>
                    <!-- x1,x2 ... xn -->
                    <xsl:when test="contains(substring-before(
                                             normalize-space($stringa),' '),',')">
                        <xsl:value-of select="normalize-space(
                                              substring-before($stringa,','))" />
                    </xsl:when>
                    <!-- x1 x2 ... ,xn -->
                    <xsl:otherwise>
                        <xsl:value-of select="normalize-space(substring-before(
                                              normalize-space($stringa),' '))" />
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:when>
            <xsl:when test="contains($stringa,',')"> <!-- x1,x2 ... -->
                <xsl:value-of select="normalize-space(substring-before($stringa,','))" />
            </xsl:when> 
            <xsl:when test="contains(normalize-space($stringa),' ')"><!-- x1 x2 ... -->
                <xsl:value-of select="normalize-space(substring-before(
                                      normalize-space($stringa),' '))" />
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="normalize-space($stringa)" />
            </xsl:otherwise>   
        </xsl:choose>
    </xsl:variable>
    
    <xsl:choose>
        <xsl:when test="contains($stringa-after,',') or
                        contains(normalize-space($stringa-after),' ')">
            <xsl:call-template name="penultimo-valore">
                <xsl:with-param name="stringa">
                    <xsl:value-of select="normalize-space($stringa-after)" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
        <xsl:otherwise>
            <xsl:value-of select="$stringa-before" />
        </xsl:otherwise>        
    </xsl:choose>
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: SOMMA VALORI *************************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="somma-valori">
    <xsl:param name="stringa" />
    <xsl:param name="somma"><xsl:text>0</xsl:text></xsl:param>
    <!-- somma tutti i valori presenti in stringa (stringa può contenere molti 
         valori, anche 0) -->
    <!-- funzione ricorsiva -->
    <xsl:variable name="stringa-after">
        <xsl:choose>
            <xsl:when test="contains($stringa,',') and 
                            contains(normalize-space($stringa),' ')">
                <xsl:choose>
                    <!-- x1,x2 ... xn -->
                    <xsl:when test="contains(substring-before(
                                             normalize-space($stringa),' '),',')">
                        <xsl:value-of select="normalize-space(
                                              substring-after($stringa,','))" />
                    </xsl:when>
                    <!-- x1 x2 ... ,xn -->
                    <xsl:otherwise>
                        <xsl:value-of select="normalize-space(substring-after(
                                              normalize-space($stringa),' '))" />
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:when>
            <xsl:when test="contains($stringa,',')"> <!-- x1,x2 ... -->
                <xsl:value-of select="normalize-space(substring-after($stringa,','))" />
            </xsl:when> 
            <xsl:when test="contains(normalize-space($stringa),' ')"><!-- x1 x2 ... -->
                <xsl:value-of select="normalize-space(substring-after(
                                      normalize-space($stringa),' '))" />
            </xsl:when>
            <xsl:otherwise>
                <xsl:text>0</xsl:text>
            </xsl:otherwise>   
        </xsl:choose>
    </xsl:variable>
    
    <xsl:variable name="stringa-before">
        <xsl:choose>
            <xsl:when test="contains($stringa,',') and 
                            contains(normalize-space($stringa),' ')">
                <xsl:choose>
                    <!-- x1,x2 ... xn -->
                    <xsl:when test="contains(substring-before(
                                             normalize-space($stringa),' '),',')">
                        <xsl:value-of select="normalize-space(
                                              substring-before($stringa,','))" />
                    </xsl:when>
                    <!-- x1 x2 ... ,xn -->
                    <xsl:otherwise>
                        <xsl:value-of select="normalize-space(substring-before(
                                              normalize-space($stringa),' '))" />
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:when>
            <xsl:when test="contains($stringa,',')"> <!-- x1,x2 ... -->
                <xsl:value-of select="normalize-space(substring-before($stringa,','))" />
            </xsl:when> 
            <xsl:when test="contains(normalize-space($stringa),' ')"><!-- x1 x2 ... -->
                <xsl:value-of select="normalize-space(substring-before(
                                      normalize-space($stringa),' '))" />
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="normalize-space($stringa)" />
            </xsl:otherwise>   
        </xsl:choose>
    </xsl:variable>
    
    <xsl:choose>
        <xsl:when test="contains($stringa-after,',') or
                        contains(normalize-space($stringa-after),' ')">
            <xsl:call-template name="somma-valori">
                <xsl:with-param name="stringa">
                    <xsl:value-of select="normalize-space($stringa-after)" />
                </xsl:with-param>
                <xsl:with-param name="somma">
                    <xsl:choose>
                        <xsl:when test="contains($stringa-before,'-')">
                            <xsl:value-of select="$somma - 
                                                  normalize-space(
                                                  substring-after($stringa-before,'-'))" />
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:value-of select="$somma + 
                                                  normalize-space($stringa-before)" />
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
        <xsl:otherwise>
            <xsl:choose>
                <xsl:when test="contains($stringa-before,'-') and 
                                contains($stringa-after,'-')">
                    <xsl:value-of select="$somma - 
                                          normalize-space(
                                          substring-after($stringa-before,'-')) - 
                                          normalize-space(
                                          substring-after($stringa-after,'-'))" />
                </xsl:when>
                <xsl:when test="contains($stringa-before,'-')">
                    <xsl:value-of select="$somma - 
                                          normalize-space(
                                          substring-after($stringa-before,'-')) +
                                          normalize-space($stringa-after)" />
                </xsl:when>
                <xsl:when test="contains($stringa-after,'-')">
                    <xsl:value-of select="$somma - 
                                          normalize-space(
                                          substring-after($stringa-after,'-')) +
                                          normalize-space($stringa-before)" />
                </xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="$somma + normalize-space($stringa-before) + 
                                          normalize-space($stringa-after)" />
                </xsl:otherwise>
            </xsl:choose>
        </xsl:otherwise>        
    </xsl:choose>
</xsl:template>



</xsl:stylesheet>
