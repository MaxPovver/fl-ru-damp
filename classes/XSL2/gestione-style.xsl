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
        - var-style-type
        - ricerca nome class
        - ricerca-style
        - gestione-nome-attributo-class
        - valore-attributo (da gestire ogni attributo ad hoc)
-->

<!-- non completo, supporta parecchi attributi e parecchi selettori usabili in style -->

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: var-style-type ************************************* -->
<!-- ******************************************************************************** -->
<xsl:template name="var-style-type">
    <xsl:param name="attributo" />
    <!-- cerca attributo nell'elemento style (in presenza di attributi id, class, ...) e
         restituisce un elemento di testo per sapere a quale tipo di selettore riferirsi.
         La precedenza è data da: 1. attributo style nell'elemento
                                  2. attributo id nell'elemento riferito nell'elemento style
                                  3. attributo class
                                  4. nome dell'elemento all'interno dell'elemento style
         L'attributo style è gestito a parte (cioè non viene restituito nessun indicatore
            da questa funzione). Il riferimento a id è segnalato dalla lettera i, quello
            a class da c, quello al nome dell'elemento con e, in assenza di ciascuno dei 3,
            si usa il segnalatore n (none).
    -->
    <!-- non gestiti i casi in cui in style sia presente come selettore * !!!!! -->
    <xsl:variable name="nome-elemento">
        <xsl:value-of select="name()" />
    </xsl:variable>
    <xsl:if test="@id">
        <xsl:variable name="valore-id">
            <xsl:value-of select="@id" />
        </xsl:variable>
        <xsl:for-each select="//svg:style">
            <xsl:if test="contains(substring-before(
                          substring-after(.,concat('#',$valore-id,' ')),'}'),
                          concat($attributo,' ')) or 
                          contains(substring-before(
                          substring-after(.,concat('#',$valore-id,' ')),'}'),
                          concat($attributo,':')) or
                          contains(substring-before(
                          substring-after(.,concat('#',$valore-id,'{')),'}'),
                          concat($attributo,' ')) or 
                          contains(substring-before(
                          substring-after(.,concat('#',$valore-id,'{')),'}'),
                          concat($attributo,':'))">
                <xsl:text>i</xsl:text>
            </xsl:if>              
        </xsl:for-each>
    </xsl:if>
    <xsl:if test="@class">
        <xsl:call-template name="ricerca-nome-class">
            <xsl:with-param name="class-value">
                <xsl:value-of select="@class" />
            </xsl:with-param>
            <xsl:with-param name="attributo">
                <xsl:value-of select="$attributo" />
            </xsl:with-param> 
        </xsl:call-template>
    </xsl:if>
    <xsl:for-each select="//svg:style">
            <xsl:if test="contains(substring-before(
                          substring-after(.,concat(' ',$nome-elemento,' ')),'}'),
                          concat($attributo,' ')) or 
                          contains(substring-before(
                          substring-after(.,concat(' ',$nome-elemento,' ')),'}'),
                          concat($attributo,':')) or
                          contains(substring-before(
                          substring-after(.,concat(' ',$nome-elemento,'{')),'}'),
                          concat($attributo,' ')) or 
                          contains(substring-before(
                          substring-after(.,concat(' ',$nome-elemento,'{')),'}'),
                          concat($attributo,':'))">
                <xsl:text>e</xsl:text>
            </xsl:if>              
        </xsl:for-each>
    <xsl:text>n</xsl:text>
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: ricerca nome class ********************************* -->
<!-- ******************************************************************************** -->
<xsl:template name="ricerca-nome-class">
    <xsl:param name="class-value" />
    <xsl:param name="attributo" />
    
<!-- l'attributo class potrebbe riferirsi a più classi, quindi devo gestire ogni 
      riferimenti.
      Questa è una funzione ricorsiva, che estrae tutti i valori di class e per
      ognuno cerca un eventuale riferimento in ogni elemento style, cercando se in quel
      particolare style è definita una proprietà associata ad attributo (passato in
      input). Nel caso venga trovato un riferimento, restituisco il carattere c, a
      segnalere il fatto che è presente un riferimento all'attributo che sto cercando
      in un elemento style, riferito tramite l'attributo class -->
    
<xsl:choose>
<xsl:when test="contains($class-value,' ') or contains($class-value,',')">
    <xsl:variable name="valore-first-class">
        <xsl:choose>
            <xsl:when test="contains($class-value,' ') and contains($class-value,',')">
                <xsl:choose>
                    <xsl:when test="contains(substring-after($class-value,' '),',')">
                        <xsl:value-of select="normalize-space(
                                              substring-before($class-value,','))" />
                    </xsl:when>
                    <xsl:otherwise> <!-- c'è prima lo spazio -->
                        <xsl:value-of select="normalize-space(
                                              substring-before($class-value,' '))" />
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:when>
            <xsl:when test="contains($class-value,' ')">
                    <xsl:value-of select="normalize-space(
                                          substring-before($class-value,' '))" />
            </xsl:when>
            <xsl:otherwise> <!-- solo , -->
                    <xsl:value-of select="normalize-space(
                                          substring-before($class-value,','))" />
            </xsl:otherwise>
        </xsl:choose>
    </xsl:variable>
    <xsl:variable name="valore-other-class">
      <xsl:choose>
            <xsl:when test="contains($class-value,' ') and contains($class-value,',')">
                <xsl:choose>
                    <xsl:when test="contains(substring-after($class-value,' '),',')">
                        <xsl:value-of select="normalize-space(
                                              substring-after($class-value,','))" />
                    </xsl:when>
                    <xsl:otherwise> <!-- c'è prima lo spazio -->
                        <xsl:value-of select="normalize-space(
                                              substring-after($class-value,' '))" />
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:when>
            <xsl:when test="contains($class-value,' ')">
                    <xsl:value-of select="normalize-space(
                                          substring-after($class-value,' '))" />
            </xsl:when>
            <xsl:otherwise> <!-- solo , -->
                    <xsl:value-of select="normalize-space(
                                          substring-after($class-value,','))" />
            </xsl:otherwise>
        </xsl:choose>
    </xsl:variable>
    
    <xsl:variable name="valore-class">
            <xsl:value-of select="$valore-first-class" />
    </xsl:variable>     
    
        <xsl:for-each select="//svg:style">
            <xsl:if test="contains(substring-before(
                          substring-after(.,concat('.',$valore-class,' ')),'}'),
                          concat($attributo,' ')) or 
                          contains(substring-before(
                          substring-after(.,concat('.',$valore-class,' ')),'}'),
                          concat($attributo,':')) or 
                          contains(substring-before(
                          substring-after(.,concat('.',$valore-class,'{')),'}'),
                          concat($attributo,' ')) or 
                          contains(substring-before(
                          substring-after(.,concat('.',$valore-class,'{')),'}'),
                          concat($attributo,':'))">
                <xsl:text>c</xsl:text>
            </xsl:if>              
        </xsl:for-each>

    <xsl:call-template name="ricerca-nome-class">
        <xsl:with-param name="class-value">
            <xsl:value-of select="normalize-space($valore-other-class)" />
        </xsl:with-param>
        <xsl:with-param name="attributo">
            <xsl:value-of select="$attributo" />
        </xsl:with-param> 
    </xsl:call-template>
    
</xsl:when>
<xsl:otherwise>
        <xsl:variable name="valore-class">
            <xsl:value-of select="$class-value" />
        </xsl:variable>
        <xsl:for-each select="//svg:style">
            <xsl:if test="contains(substring-before(
                          substring-after(.,concat('.',$valore-class,' ')),'}'),
                          concat($attributo,' ')) or 
                          contains(substring-before(
                          substring-after(.,concat('.',$valore-class,' ')),'}'),
                          concat($attributo,':')) or 
                          contains(substring-before(
                          substring-after(.,concat('.',$valore-class,'{')),'}'),
                          concat($attributo,' ')) or 
                          contains(substring-before(
                          substring-after(.,concat('.',$valore-class,'{')),'}'),
                          concat($attributo,':'))">
                <xsl:text>c</xsl:text>
            </xsl:if>              
        </xsl:for-each>
</xsl:otherwise>
</xsl:choose>
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: ricerca-style ************************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="ricerca-style">
    <xsl:param name="attributo" />
    <xsl:param name="style-type" />
    
<!-- cerco la proprietà associata ad attributo o nell'attributo style o in un
     elemento style. Questa funzione è chiamata SOLO nel caso ci sia un 
     effettivo riferimento ad attributo. 
     La priorità della ricerca è data all'attributo style, se in esso non ci si
      riferisce ad attributo si analizza il contenuto di style-type, che conterra come
      iniziale, una lettera (i,c o e) a segnalare se devo cercare l'attributo in 
      un elemento style, con un selettore che si riferisce al valore dell'attributo
      id (dell'elemento corrente), a class, oppure al nome dell'elemento.
     Una volta trovato l'attributo e il valore associato, viene passato ad un opportuno
     template (valore-attributo) che estrae il valore ed eventualmente modificandolo
     lo restituisce; l'applicazione (template) chiamante lo dovrà gestire opportunamente
     inserendolo in un qualche attributo.
-->
    
<xsl:choose>
<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<!-- XXXXXXXXXXXXXXXXXXXXXXX ATTRIBUTO STYLE XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<xsl:when test="@style and (contains(@style, concat($attributo,' ')) or 
                contains(@style, concat($attributo,':')))">

    <xsl:call-template name="valore-attributo">
            <xsl:with-param name="stringa">
                <xsl:value-of select="@style" />
            </xsl:with-param>
            <xsl:with-param name="attributo">
                <xsl:value-of select="$attributo" />
            </xsl:with-param>            
    </xsl:call-template>
    
</xsl:when>
<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<!-- XXXXXXXXXXXXXXXXXXXXXXX ATTRIBUTO ID XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<xsl:when test="@id and substring($style-type,1,1) = 'i'">
    <!-- devo andare nell'ultimo elemento style che contiene id + $attributo -->
    <xsl:variable name="valore-id">
            <xsl:text>#</xsl:text>
            <xsl:value-of select="@id" />
    </xsl:variable>             
                                    
    <xsl:variable name="valore-style">
        <xsl:for-each select="//svg:style[contains(substring-before(
                                   substring-after(.,concat($valore-id,' '))
                                   ,'}'),concat($attributo,' ')) or
                                   contains(substring-before(
                                   substring-after(.,concat($valore-id,' ')),
                                   '}'),concat($attributo,':'))or 
                                   contains(substring-before(
                                   substring-after(.,concat($valore-id,'{'))
                                   ,'}'),concat($attributo,' ')) or
                                   contains(substring-before(
                                   substring-after(.,concat($valore-id,'{')),
                                   '}'),concat($attributo,':'))]">
            <xsl:if test="position() = last()">
                <xsl:choose>
                    <xsl:when test="contains(substring-before(
                                   substring-after(.,concat($valore-id,' '))
                                   ,'}'),concat($attributo,' ')) or
                                   contains(substring-before(
                                   substring-after(.,concat($valore-id,' ')),
                                   '}'),concat($attributo,':'))">        
                        <xsl:value-of select="substring-before(
                                    substring-after(
                                    substring-after(.,concat($valore-id,' ')),'{'),'}')" />
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="substring-before(
                                    substring-after(.,concat($valore-id,'{')),'}')" />
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:if>
        </xsl:for-each>
    </xsl:variable>
    
    <xsl:call-template name="valore-attributo">
            <xsl:with-param name="stringa">
                <xsl:value-of select="$valore-style" />
            </xsl:with-param>
            <xsl:with-param name="attributo">
                <xsl:value-of select="$attributo" />
            </xsl:with-param>   
    </xsl:call-template>
    
</xsl:when>
<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<!-- XXXXXXXXXXXXXXXXXXXXXXX ATTRIBUTO CLASS XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<xsl:when test="@class and substring($style-type,1,1) = 'c'">
    <!-- devo andare nell'ultimo elemento style che contiene class + $attributo -->
    
    <!-- da fare ricorsivamente, class potrebbe contenere piu' identificatori -->
    
    <xsl:call-template name="gestione-nome-attributo-class">
        <xsl:with-param name="class-value">
            <xsl:value-of select="@class" />
        </xsl:with-param>
        <xsl:with-param name="attributo">
            <xsl:value-of select="$attributo" />
        </xsl:with-param>   
    </xsl:call-template>
    
</xsl:when>
<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<!-- XXXXXXXXXXXXXXXXXXXXXXX ELEMENTO in STYLE XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
<xsl:when test="substring($style-type,1,1) = 'e'">
    <!-- devo andare nell'ultimo elemento style che contiene l'elemento come selettore
         + $attributo -->
    
    <xsl:variable name="nome-el">
            <xsl:value-of select="name()" />
    </xsl:variable>             
                                    
    <xsl:variable name="valore-style">
        <xsl:for-each select="//svg:style[contains(substring-before(
                          substring-after(.,concat(' ',$nome-el,' ')),'}'),
                          concat($attributo,' ')) or 
                          contains(substring-before(
                          substring-after(.,concat(' ',$nome-el,' ')),'}'),
                          concat($attributo,':')) or
                          contains(substring-before(
                          substring-after(.,concat(' ',$nome-el,'{')),'}'),
                          concat($attributo,' ')) or 
                          contains(substring-before(
                          substring-after(.,concat(' ',$nome-el,'{')),'}'),
                          concat($attributo,':'))]">
            <xsl:if test="position() = last()">
                <xsl:choose>
                    <xsl:when test="contains(substring-before(
                                   substring-after(.,concat(' ',$nome-el,' '))
                                   ,'}'),concat($attributo,' ')) or
                                   contains(substring-before(
                                   substring-after(.,concat(' ',$nome-el,' ')),
                                   '}'),concat($attributo,':'))">        
                        <xsl:value-of select="substring-before(
                                    substring-after(
                                    substring-after(.,concat(' ',$nome-el,' '))
                                    ,'{'),'}')" />
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="substring-before(
                                    substring-after(.,concat(' ',$nome-el,'{')),'}')" />
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:if>
        </xsl:for-each>
    </xsl:variable>

    <xsl:call-template name="valore-attributo">
            <xsl:with-param name="stringa">
                <xsl:value-of select="$valore-style" />
            </xsl:with-param>
            <xsl:with-param name="attributo">
                <xsl:value-of select="$attributo" />
            </xsl:with-param>   
    </xsl:call-template>
</xsl:when>
</xsl:choose>
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: gestione nome attributo class ********************** -->
<!-- ******************************************************************************** -->
<xsl:template name="gestione-nome-attributo-class">
    <xsl:param name="class-value" />
    <xsl:param name="attributo" />
    
<!-- come ricerca-nome-class, solo che una volta trovato l'attributo e il valore
      lo passa al template (valore-attributo) che estrae il valore e lo restituisce,
      eventualemente modificandolo.
-->
    
<xsl:choose>
<xsl:when test="contains($class-value,' ') or contains($class-value,',')">

    <xsl:variable name="valore-first-class">
        <xsl:choose>
            <xsl:when test="contains($class-value,' ') and contains($class-value,',')">
                <xsl:choose>
                    <xsl:when test="contains(substring-after($class-value,' '),',')">
                        <xsl:value-of select="normalize-space(
                                              substring-before($class-value,','))" />
                    </xsl:when>
                    <xsl:otherwise> <!-- c'è prima lo spazio -->
                        <xsl:value-of select="normalize-space(
                                              substring-before($class-value,' '))" />
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:when>
            <xsl:when test="contains($class-value,' ')">
                    <xsl:value-of select="normalize-space(
                                          substring-before($class-value,' '))" />
            </xsl:when>
            <xsl:otherwise> <!-- solo , -->
                    <xsl:value-of select="normalize-space(
                                          substring-before($class-value,','))" />
            </xsl:otherwise>
        </xsl:choose>
    </xsl:variable>
    <xsl:variable name="valore-other-class">
      <xsl:choose>
            <xsl:when test="contains($class-value,' ') and contains($class-value,',')">
                <xsl:choose>
                    <xsl:when test="contains(substring-after($class-value,' '),',')">
                        <xsl:value-of select="normalize-space(
                                              substring-after($class-value,','))" />
                    </xsl:when>
                    <xsl:otherwise> <!-- c'è prima lo spazio -->
                        <xsl:value-of select="normalize-space(
                                              substring-after($class-value,' '))" />
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:when>
            <xsl:when test="contains($class-value,' ')">
                    <xsl:value-of select="normalize-space(
                                          substring-after($class-value,' '))" />
            </xsl:when>
            <xsl:otherwise> <!-- solo , -->
                    <xsl:value-of select="normalize-space(
                                          substring-after($class-value,','))" />
            </xsl:otherwise>
        </xsl:choose>
    </xsl:variable>
    
    <xsl:variable name="valore-class">
            <xsl:text>.</xsl:text>
            <xsl:value-of select="$valore-first-class" />
    </xsl:variable>     
    
    <xsl:variable name="valore-style">
        <xsl:for-each select="//svg:style[contains(substring-before(
                                   substring-after(.,concat($valore-class,' '))
                                   ,'}'),concat($attributo,' ')) or
                                   contains(substring-before(
                                   substring-after(.,concat($valore-class,' ')),
                                   '}'),concat($attributo,':'))or 
                                   contains(substring-before(
                                   substring-after(.,concat($valore-class,'{'))
                                   ,'}'),concat($attributo,' ')) or
                                   contains(substring-before(
                                   substring-after(.,concat($valore-class,'{')),
                                   '}'),concat($attributo,':'))]">
            <xsl:if test="position() = last()">
                <xsl:choose>
                    <xsl:when test="contains(substring-before(
                                   substring-after(.,concat($valore-class,' '))
                                   ,'}'),concat($attributo,' ')) or
                                   contains(substring-before(
                                   substring-after(.,concat($valore-class,' ')),
                                   '}'),concat($attributo,':'))">        
                        <xsl:value-of select="substring-before(
                                    substring-after(
                                    substring-after(.,concat($valore-class,' '))
                                    ,'{'),'}')" />
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="substring-before(
                                    substring-after(.,concat($valore-class,'{')),'}')" />
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:if>
        </xsl:for-each>
    </xsl:variable>
    
    <xsl:if test="$valore-style != ''">
    <xsl:call-template name="valore-attributo">
            <xsl:with-param name="stringa">
                <xsl:value-of select="$valore-style" />
            </xsl:with-param>
            <xsl:with-param name="attributo">
                <xsl:value-of select="$attributo" />
            </xsl:with-param>   
    </xsl:call-template>
    </xsl:if>
    
    <xsl:call-template name="gestione-nome-attributo-class">
        <xsl:with-param name="class-value">
            <xsl:value-of select="normalize-space($valore-other-class)" />
        </xsl:with-param>
        <xsl:with-param name="attributo">
            <xsl:value-of select="$attributo" />
        </xsl:with-param>  
    </xsl:call-template>
</xsl:when>

<xsl:otherwise>
   <xsl:variable name="valore-class">
            <xsl:text>.</xsl:text>
            <xsl:value-of select="$class-value" />
    </xsl:variable>             
                                    
    <xsl:variable name="valore-style">
        <xsl:for-each select="//svg:style[contains(substring-before(
                                   substring-after(.,concat($valore-class,' '))
                                   ,'}'),concat($attributo,' ')) or
                                   contains(substring-before(
                                   substring-after(.,concat($valore-class,' ')),
                                   '}'),concat($attributo,':'))or 
                                   contains(substring-before(
                                   substring-after(.,concat($valore-class,'{'))
                                   ,'}'),concat($attributo,' ')) or
                                   contains(substring-before(
                                   substring-after(.,concat($valore-class,'{')),
                                   '}'),concat($attributo,':'))]">
            <xsl:if test="position() = last()">
                <xsl:choose>
                    <xsl:when test="contains(substring-before(
                                   substring-after(.,concat($valore-class,' '))
                                   ,'}'),concat($attributo,' ')) or
                                   contains(substring-before(
                                   substring-after(.,concat($valore-class,' ')),
                                   '}'),concat($attributo,':'))">        
                        <xsl:value-of select="substring-before(
                                    substring-after(
                                    substring-after(.,concat($valore-class,' '))
                                    ,'{'),'}')" />
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="substring-before(
                                    substring-after(.,concat($valore-class,'{')),'}')" />
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:if>
        </xsl:for-each>
    </xsl:variable>
    
    <xsl:if test="$valore-style != ''">
    <xsl:call-template name="valore-attributo">
            <xsl:with-param name="stringa">
                <xsl:value-of select="$valore-style" />
            </xsl:with-param>
            <xsl:with-param name="attributo">
                <xsl:value-of select="$attributo" />
            </xsl:with-param>   
    </xsl:call-template>
    </xsl:if>
</xsl:otherwise>
</xsl:choose>

</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: valore-attributo *********************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="valore-attributo" >
    <xsl:param name="stringa" />
    <xsl:param name="attributo" />
    <!-- cerca all'interno di stringa il valore dell'elemento attributo (che sarà nella
         forma attributo: valore; e restituisce questo valore, eventualmente modificandolo,
         oppure creando opportuni attributi.
    -->
    
<xsl:variable name="valore-attributo">
    <xsl:choose>
    <xsl:when test="contains($stringa,concat($attributo,' '))">  
        <xsl:choose>
            <xsl:when test="contains(substring-after($stringa,concat($attributo,' ')),';')">
                <xsl:value-of select="normalize-space(
                                      substring-before(
                                      substring-after(
                                      substring-after($stringa,concat($attributo,' '))
                                      ,':'),';'))" />
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="normalize-space(
                                      substring-after(
                                      substring-after($stringa,concat($attributo,' '))
                                      ,':'))" />
            </xsl:otherwise>
        </xsl:choose>
    </xsl:when>
    <xsl:otherwise><!-- attributo: -->
        <xsl:choose>
            <xsl:when test="contains(substring-after($stringa,concat($attributo,':')),';')">
                <xsl:value-of select="normalize-space(
                                      substring-before(
                                      substring-after($stringa,concat($attributo,':'))
                                      ,';'))" />
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="normalize-space(
                                      substring-after($stringa,concat($attributo,':')))" />
            </xsl:otherwise>
        </xsl:choose>
    </xsl:otherwise>
    </xsl:choose>
</xsl:variable>

<xsl:choose>
    <xsl:when test="($attributo = 'fill') or ($attributo = 'stroke')">
        <xsl:choose>
            <xsl:when test="substring($valore-attributo,1,3) = 'url'">
                <xsl:variable name="nome-el">
                    <xsl:value-of select="substring($valore-attributo,6,
                                          string-length($valore-attributo) - 6)" />
                </xsl:variable>
            
                <xsl:attribute name="on"><xsl:text>true</xsl:text></xsl:attribute>
            
                <xsl:call-template name="gradient-pattern-ref">
                    <xsl:with-param name="nome">
                        <xsl:value-of select="$nome-el" />
                    </xsl:with-param>
                </xsl:call-template>

            </xsl:when>
            <xsl:when test="normalize-space($valore-attributo) = 'none'">
                <xsl:attribute name="on"><xsl:text>false</xsl:text></xsl:attribute>
            </xsl:when>
            <xsl:otherwise>
                <xsl:attribute name="on"><xsl:text>true</xsl:text></xsl:attribute>
                    <xsl:attribute name="color">
                        <xsl:value-of select="$valore-attributo" />
                    </xsl:attribute>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:when>
    <!-- XXXXXXXXXXXXXXX da gestire ogni altro caso (ogni elemento il cui nome è diverso
                         per svg e vml)
         XXXXXXXXXXXXXXX -->
    <xsl:when test="$attributo = 'stroke-width'">
        <xsl:value-of select="$valore-attributo" />
    </xsl:when>
    <xsl:when test="$attributo = 'font-size'">
        <xsl:value-of select="$valore-attributo" />
    </xsl:when>
    <xsl:when test="$attributo = 'font-family'">
        <xsl:value-of select="$valore-attributo" />
    </xsl:when>
    <xsl:when test="$attributo = 'font-style'">
        <xsl:value-of select="$valore-attributo" />
    </xsl:when>
    <xsl:when test="$attributo = 'font-weight'">
        <xsl:value-of select="$valore-attributo" />
    </xsl:when>
    <xsl:when test="$attributo = 'font-variant'">
        <xsl:value-of select="$valore-attributo" />
    </xsl:when>
    <xsl:when test="$attributo = 'text-anchor'">
        <xsl:value-of select="$valore-attributo" />
    </xsl:when>
    <xsl:when test="$attributo = 'text-decoration'">
        <xsl:value-of select="$valore-attributo" />
    </xsl:when>
    <xsl:when test="$attributo = 'opacity'">
        <xsl:value-of select="$valore-attributo" />
    </xsl:when>
    <xsl:when test="$attributo = 'fill-opacity'">
        <xsl:value-of select="$valore-attributo" />
    </xsl:when>
    <xsl:when test="$attributo = 'stroke-opacity'">
        <xsl:value-of select="$valore-attributo" />
    </xsl:when>
    <xsl:when test="$attributo = 'stroke-dasharray'">
        <xsl:attribute name="dashstyle">
            <xsl:value-of select="$valore-attributo" />
        </xsl:attribute>
    </xsl:when>
        <xsl:when test="$attributo = 'stroke-linecap'">
        <xsl:attribute name="endcap">
            <xsl:choose>
                <xsl:when test="$valore-attributo = 'butt'">
                    <xsl:text>flat</xsl:text>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="$valore-attributo" />
                </xsl:otherwise>
            </xsl:choose>
        </xsl:attribute>
    </xsl:when>
    <xsl:when test="$attributo = 'stroke-linejoin'">
        <xsl:attribute name="joinstyle">
            <xsl:value-of select="$valore-attributo" />
        </xsl:attribute>
    </xsl:when>
    <xsl:when test="$attributo = 'stroke-miterlimit'">
        <xsl:attribute name="miterlimit">
            <xsl:value-of select="$valore-attributo" />
        </xsl:attribute>
    </xsl:when>
    <xsl:otherwise>
        <xsl:attribute name="{$attributo}">
            <xsl:value-of select="$valore-attributo" />
        </xsl:attribute>
    </xsl:otherwise>
</xsl:choose>

</xsl:template>

</xsl:stylesheet>
