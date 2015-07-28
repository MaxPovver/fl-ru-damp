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
        - gradient-pattern-ref
   * match:
        - svg:pattern
-->

<!-- problemi con pattern: il path e' inserito in un gruppo che non viene visualizzato,
     il problema è riuscire a riferirsi a quel gruppo: non appare realizzabile
-->

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: gradient-pattern-ref ******************************* -->
<!-- ******************************************************************************** -->
<xsl:template name="gradient-pattern-ref">
    <xsl:param name="nome" />
    <xsl:param name="attributo"><xsl:text>fill</xsl:text></xsl:param>
    <!-- contiene il nome dell'attibuto che ha chiamato il template, di default e' fill -->
    
<!-- cerca in defs l'elemento con l'id uguale al parametro nome. In base all'elemento
     (linearGradient, radialGradient, Pattern) imposta i vari attributi
-->
<!-- lo consideriamo chiamato solo da fill e stroke, 
      bisogna gestire eventualmente gli altri elementi 
-->

<xsl:for-each select="//*[@id]">
<xsl:if test="@id = $nome">

    <!-- siamo nell'elemento riferito dall'elemento -->
    <xsl:choose>
    <!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX FILL XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
    <!-- gradiente riferito per proprietà di fill:
            in base al tipo di elemento (linear o radial gradient) e ai valori (degli
            attributi) di quest'elemento imposto vari attributi, cercando di rendere
            la traduzione più simile possibile. -->
    <xsl:when test="$attributo = 'fill'">
        <xsl:choose>
        <xsl:when test="name() = 'linearGradient'">
            <xsl:attribute name="type">
                <xsl:text>gradient</xsl:text>
            </xsl:attribute>
                                
            <xsl:attribute name="method">
                <xsl:text>sigma</xsl:text>
            </xsl:attribute>
            
            <!-- svg e vml hanno un diverso modo di disporre i colori del gradiente, un
                 linguaggio li mette in verticale, l'altro in orizzontale, impostando
                 quest'attributo si fa in modo di posizionare i colori nella stessa 
                 maniera -->
            <xsl:attribute name="angle">
                <xsl:text>-270</xsl:text>
            </xsl:attribute>  
                                
            <!--
            <xsl:attribute name="focus">
                <xsl:text>100%</xsl:text>
            </xsl:attribute>  
            -->
            
            
            <xsl:attribute name="colors">
                <xsl:for-each select="svg:stop">
                    <xsl:value-of select="@offset" />
                    <xsl:text> </xsl:text>
                    <xsl:choose>
                        <xsl:when test="@stop-color"> 
                            <xsl:value-of select="@stop-color" />
                        </xsl:when>
                        <xsl:when test="contains(@style,'stop-color')">
                            <xsl:variable name="style-temp">
                                <xsl:value-of select="substring-after(
                                    substring-after(@style,'stop-color'),':')" />
                            </xsl:variable>
                            <xsl:choose>
                                <xsl:when test="contains($style-temp,';')">
                                    <xsl:value-of select="normalize-space(
                                        substring-before($style-temp,';'))" />
                                </xsl:when>
                                <xsl:otherwise>
                                    <xsl:value-of select="normalize-space(style-temp)" />
                                </xsl:otherwise>
                            </xsl:choose>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:text>white</xsl:text>
                        </xsl:otherwise>
                    </xsl:choose>
                    <xsl:if test="position() != last()">
                        <xsl:text>, </xsl:text>
                    </xsl:if>
                    <!-- l'attributo stop-opacity non è supportato da vml !!!! -->
                </xsl:for-each>
            </xsl:attribute>
        </xsl:when>
        <xsl:when test="name() = 'radialGradient'">
            <xsl:attribute name="type">
                <xsl:text>gradientradial</xsl:text>
            </xsl:attribute>
            <!--
            <xsl:attribute name="focussize">
                <xsl:value-of select="@cx" />
                <xsl:text> </xsl:text>
                <xsl:value-of select="@cy" />
            </xsl:attribute>
            -->
            <xsl:attribute name="focusposition">
                <xsl:text>50% 50%</xsl:text>
            </xsl:attribute>
                                
            <xsl:attribute name="method">
                <xsl:text>sigma</xsl:text>
            </xsl:attribute>                            
                                                           
            <xsl:attribute name="colors">
                <xsl:for-each select="svg:stop">
                    <xsl:value-of select="@offset" />
                    <xsl:text> </xsl:text>
                     <xsl:choose>
                        <xsl:when test="@stop-color"> 
                            <xsl:value-of select="@stop-color" />
                        </xsl:when>
                        <xsl:when test="contains(@style,'stop-color')">
                            <xsl:variable name="style-temp">
                                <xsl:value-of select="substring-after(
                                    substring-after(@style,'stop-color'),':')" />
                            </xsl:variable>
                            <xsl:choose>
                                <xsl:when test="contains($style-temp,';')">
                                    <xsl:value-of select="normalize-space(
                                        substring-before($style-temp,';'))" />
                                </xsl:when>
                                <xsl:otherwise>
                                    <xsl:value-of select="normalize-space(style-temp)" />
                                </xsl:otherwise>
                            </xsl:choose>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:text>white</xsl:text>
                        </xsl:otherwise>
                    </xsl:choose>
                    <xsl:if test="position() != last()">
                        <xsl:text>, </xsl:text>
                    </xsl:if>
                    <!-- l'attributo stop-opacity non è supportato da vml !!!! -->
                </xsl:for-each>
            </xsl:attribute>
        </xsl:when>
        <xsl:when test="name() = 'pattern'">
            <xsl:attribute name="type">
                <xsl:text>frame</xsl:text>
            </xsl:attribute>
            
            <xsl:attribute name="src">
                <xsl:for-each select="*[@xlink:href]">
                    <xsl:if test="position() = last()">
                        <xsl:value-of select="@xlink:href" />
                    </xsl:if>
                </xsl:for-each>
            </xsl:attribute>   
            
        </xsl:when>
        <!-- da gestire gli altri casi... -->
        <xsl:otherwise>
        </xsl:otherwise>
        </xsl:choose>
    </xsl:when>
    <!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX STROKE XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
    <!-- gradient riferito a stroke:
          vml non supporta il gradiente per stroke, nel caso ci fosse il riferimento in svg
            per la traduzione si effettua un bordo inserendo come colore, il primo
            colore del gradiente. -->
    <xsl:when test="$attributo='stroke'">
        <xsl:choose>
        <xsl:when test="name() = 'linearGradient' or name() = 'radialGradient'">
    
            <!--
            <xsl:attribute name="filltype">
                <xsl:text>pattern</xsl:text>
            </xsl:attribute>   
            
            <xsl:attribute name="src">
                <xsl:text>url(#</xsl:text>
                <xsl:value-of select="$nome" />
                <xsl:text>)</xsl:text>
            </xsl:attribute>   
            -->

            <!-- visto che non si riesce a gestire gradient, mettiamoci almeno un colore -->
            <xsl:attribute name="color">
                <xsl:for-each select="svg:stop">
                    <xsl:if test="position() = '1'">
                        <xsl:value-of select="@stop-color" />
                    </xsl:if>
                    <!-- l'attributo stop-opacity non è supportato da vml !!!! -->
                </xsl:for-each>
            </xsl:attribute>
            
        </xsl:when>
        <xsl:when test="name() = 'pattern'">
            <xsl:attribute name="filltype">
                <xsl:text>pattern</xsl:text>
            </xsl:attribute>
            
            <xsl:attribute name="src">
                <xsl:text>url(#</xsl:text>
                <xsl:value-of select="$nome" />
                <xsl:text>)</xsl:text>
            </xsl:attribute>   
            
        </xsl:when>
        </xsl:choose>
    </xsl:when>
    <!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX ALTRI (eventuali) XXXXXXXXXXXXXXXXXXXXXXXXXXX -->
    <!-- templete chiamato ne da fill ne da stroke -->
    <xsl:otherwise>
    </xsl:otherwise>
    </xsl:choose>
</xsl:if>    
</xsl:for-each>
</xsl:template>

<xsl:template match="svg:pattern">
<!-- il contenuto di pattern viene inserito in un gruppo, ma tuttavia non si
      riesce a riferircisi.
-->
<v:group>
    <xsl:attribute name="id">
        <xsl:value-of select="@id" />
    </xsl:attribute>
    
    <xsl:call-template name="attributi-dimensione" />
   
    <xsl:if test="@viewBox">
        <xsl:call-template name="coord-origin-size" />
    </xsl:if>
   
   <xsl:call-template name="attributi-style" />
   <xsl:call-template name="attributi-core" />
   <xsl:call-template name="attributi-conditional" />
   <xsl:call-template name="attributi-graphical-event" />
   <xsl:call-template name="attributi-external" />
   <xsl:call-template name="attributi-presentation" />
   <xsl:call-template name="attributi-paint" />
    
    <xsl:apply-templates />
</v:group>
</xsl:template>


</xsl:stylesheet>
