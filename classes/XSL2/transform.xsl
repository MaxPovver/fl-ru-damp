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
        - conversione       (effettua conversioni da unita' di misura (es. cm) in user unit)
        - unita-di-misura       (unita' di misura dell'attributo)
        - solo-numero           (valore dell'attributo)
        - tipo-attributo        (valore da moltiplicare per i valori %)
        
        - transform-ric     (crea un gruppo per ogni elemento in transform)
        - transform-group       (gestisce il nuovo gruppo in base alla trasformazione)
        
        - translate-x-val   (valore di translate per la X)
        - translate-y-val   (valore di translate per la Y)
   
        - is-scale          (modifica co e cs in base al valore di scale)
        - scale-w-val           (valore di w scalto)  
        - scale-h-val           (valore di h scalto)
        - scale-x-val       (valore di scale per la X)
        - scale-y-val       (valore di scale per la Y)
   
        - normalizza-transform  (sostituisce rotate(g,x,y) con 2 translate e 1 rotate equiv)
        - modifica-rotate           (usato sopra)
        - traduci-in-rotate         (usata sopra)

        - w-group-prec          (w del gruppo precedente)
        - h-group-prec          (h del gruppo precedente)
        - x-group-prec          (x del gruppo precedente)
        - y-group-prec          (y del gruppo precedente)
        
        - calcola-val-group-prec      (scala i valori di x,y,w,h in base alle precedenti scale)     
        - ricerca-attributo-group-ric   (usato sopra)
        
        - calcola-scala         (calcola il valore di scale di tutti gli elementi precedenti)
        - calcola-valore-scale      (calcola il valore di scale di un dato elemento)
-->


<!-- ******************************************************************************** -->
<!-- ******************************************************************************** -->
<!-- ******************************************************************************** -->
<!-- ******************************************************************************** -->
<!-- ************************ TEMPLATE per conversione valori *********************** -->
<!-- ******************************************************************************** -->
<!-- ******************************************************************************** -->
<!-- ******************************************************************************** -->
<!-- ******************************************************************************** -->

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: conversione  *************************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="conversione" >
    <xsl:param name="attributo" />
    <xsl:param name="nome" />
    
<!-- gli viene passato un attributo che viene convertito in unser unit (nome attributo
        serve per gestire attributi con valori espressi in %, per sapere a cosa ci
        si deve riferire 
-->
<!-- se l'attributo è in percentuale, cerca l'attributo da convertire e lo converte -->

    <!-- dim contiene l'unità di misura di attributo -->
    <xsl:variable name="dim">
        <xsl:call-template name="unita-di-misura">
            <xsl:with-param name="attributo">
                <xsl:value-of select="$attributo" />
            </xsl:with-param>
        </xsl:call-template>
    </xsl:variable>

    <!-- number contiene il valore di attributo senza unità di misura -->
    <xsl:variable name="number">
        <xsl:call-template name="solo-numero">
            <xsl:with-param name="attributo">
                <xsl:value-of select="$attributo" />
            </xsl:with-param>
            <xsl:with-param name="dim">
                <xsl:value-of select="$dim" />
            </xsl:with-param>
        </xsl:call-template>
    </xsl:variable>

    <!-- qui restituisce il valore dell'attributo convertito in user unit -->
    <xsl:choose>
            <xsl:when test="$dim = 'cm'">
                <xsl:value-of select="$number * 35.43307" />  
            </xsl:when>
            <xsl:when test="$dim = 'in'">
                <xsl:value-of select="$number * 96" />
            </xsl:when>
            <xsl:when test="$dim = 'px'">
                <xsl:value-of select="$number" />
            </xsl:when>
            <xsl:when test="$dim = 'pt'">
                <xsl:value-of select="$number * 1.25" />
            </xsl:when>
            <xsl:when test="$dim = 'mm'">
                <xsl:value-of select="$number * 3.543307" />
            </xsl:when>
            <xsl:when test="$dim = 'pc'">
                <xsl:value-of select="$number * 15" />
            </xsl:when>
            <xsl:when test="$dim = 'em'">
                <!-- devo cercare font-size -->
                <xsl:variable name="font-s">
                    <xsl:call-template name="valore-font-size" />
                </xsl:variable>
                <xsl:value-of select="$number * $font-s" />
            </xsl:when>
            <xsl:when test="$dim = '%'">
                <!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
                <!-- cerco il valori dell'attributo da convertire e 
                    lo divido per 100 e moltiplico per number -->
                
                <xsl:variable name="attr-da-convertire">
                    <xsl:call-template name="tipo-attributo" >
                        <xsl:with-param name="nome">
                            <xsl:value-of select="$nome" />
                        </xsl:with-param>
                    </xsl:call-template>
                </xsl:variable>
                <xsl:value-of select="($attr-da-convertire div 100) * $number" />
                <!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
            </xsl:when>
            <xsl:when test="$dim = ''">
                <xsl:value-of select="$number" />
            </xsl:when>
            <xsl:otherwise>
                <!-- ex: non gestito, restituisco il valore senza unita
                         di misura -->
                <xsl:value-of select="$number" />
                <!--<xsl:value-of select="$dim" />-->
            </xsl:otherwise>
    </xsl:choose>    
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: unita di misura ************************************ -->
<!-- ******************************************************************************** -->
<xsl:template name="unita-di-misura">
    <xsl:param name="attributo" />
    <!-- restituisce l'unità di misura di attributo -->
    <xsl:variable name="dim-temp">
        <xsl:value-of select="substring($attributo,string-length($attributo) - 1, 2)" />
    </xsl:variable>
    
    <xsl:choose>
        <xsl:when test="($dim-temp = 'em') or ($dim-temp = 'ex') or 
                        ($dim-temp = 'px') or ($dim-temp = 'pt') or 
                        ($dim-temp = 'pc') or ($dim-temp = 'cm') or 
                        ($dim-temp = 'mm') or ($dim-temp = 'in')" >
                <xsl:value-of select="$dim-temp" />   
        </xsl:when>
        <xsl:when test="substring($dim-temp,2,1) = '%'">
            <xsl:text>%</xsl:text>
        </xsl:when>
        <xsl:otherwise>
            <xsl:text></xsl:text>
        </xsl:otherwise>
    </xsl:choose>
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: solo numero ************************************ -->
<!-- ******************************************************************************** -->
<xsl:template name="solo-numero">
    <xsl:param name="attributo" />
    <xsl:param name="dim" />
    <!-- restituisce il valore di attributo senza l'unità di misura (contenuta in dim) -->
    
    <xsl:choose>
        <xsl:when test="$dim = ''">
            <xsl:value-of select="$attributo" />
        </xsl:when>
        <xsl:when test="$dim = '%'">
            <xsl:value-of select="substring($attributo,1,string-length($attributo) - 1)" />
        </xsl:when>
        <xsl:otherwise>
            <xsl:value-of select="substring($attributo,1,string-length($attributo) - 2)" />
        </xsl:otherwise>
    </xsl:choose>
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: tipo attributo  ************************************ -->
<!-- ******************************************************************************** -->
<xsl:template name="tipo-attributo">
<xsl:param name="nome" />
<!-- usato in conversione quando è presente un valore percentuale, va per casi sul
    tipo di attributo da convertire, in alcuni casi lo cerca nel padre, se c'è lo 
    restituisce, altrimenti calcola il valore dell'attributo per il padre e poi 
    lo restituisce.
    In altri casi cerca negli attributi dell'elemento stesso...
    
-->

<!-- c'è da farlo per tutti i casi possibili!!! -->

<xsl:choose>

<!-- XXXXXXXXXX ATTRIBUTO: width XXXXXXXXX -->
<xsl:when test="$nome ='width'">
    <!-- controllo se non sono in svg -->
    <xsl:choose>
    <xsl:when test="ancestor::*">
        <xsl:for-each select="..">
            <xsl:choose>
                <xsl:when test="@viewBox">
                    <xsl:value-of select="substring-before(substring-after
                    (substring-after(normalize-space(@viewBox),' '),' '),' ')" />
                </xsl:when>
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
                    <xsl:call-template name="svg-width" />
                </xsl:otherwise>
            </xsl:choose>
        </xsl:for-each>
    </xsl:when>
    <xsl:otherwise>
            <xsl:value-of select="$schermo-x" />
    </xsl:otherwise>
    </xsl:choose>
</xsl:when>

<!-- XXXXXXXXXX ATTRIBUTO: height XXXXXXXXX -->
<xsl:when test="$nome ='height'">
    <xsl:choose>
        <xsl:when test="ancestor::*">
            <xsl:for-each select="..">
                <xsl:choose>
                    <xsl:when test="@viewBox">
                        <xsl:value-of select="substring-after(substring-after
                        (substring-after(normalize-space(@viewBox),' '),' '),' ')" />
                    </xsl:when>
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
                        <xsl:call-template name="svg-height" />
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:for-each>
        </xsl:when>
        <xsl:otherwise>
            <xsl:value-of select="$schermo-y" />
        </xsl:otherwise>
    </xsl:choose>        
</xsl:when>

<!-- XXXXXXXXXX ATTRIBUTO: stroke-width XXXXXXXXX -->
<xsl:when test="$nome ='stroke-width'">
    <xsl:choose>
    <xsl:when test="ancestor::*[@viewBox]">
        <xsl:for-each select="ancestor::*[@viewBox]">
            <xsl:if test="position() = last()">
                        <xsl:variable name="val-width">
                            <xsl:value-of select="substring-before(substring-after
                            (substring-after(normalize-space(@viewBox),' '),' '),' ')" />
                        </xsl:variable>
        
                        <xsl:variable name="val-height">
                            <xsl:value-of select="substring-after(substring-after
                            (substring-after(normalize-space(@viewBox),' '),' '),' ')" />
                        </xsl:variable>
                        
                        <xsl:value-of select="($val-width + $val-height) div 2" />
            </xsl:if>
        </xsl:for-each>
    </xsl:when>
    <xsl:otherwise>
            <xsl:value-of select="($schermo-x + $schermo-y) div 2" />
    </xsl:otherwise>
    </xsl:choose>
</xsl:when>

<!-- XXXXXXXXXX ATTRIBUTO: font-size XXXXXXXXX -->
<xsl:when test="$nome ='font-size'">
    <!-- controllo se non sono in svg -->
    <xsl:choose>
    <xsl:when test="ancestor::*[@font-size]">
        <xsl:for-each select="ancestor::*[@font-size]">
            <xsl:if test="position() = last()">
                <xsl:call-template name="conversione">
                    <xsl:with-param name="attributo">
                        <xsl:value-of select="@font-size" />
                    </xsl:with-param>
                    <xsl:with-param name="nome">
                        <xsl:text>font-size</xsl:text>
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:if>
        </xsl:for-each>
    </xsl:when>
    <xsl:otherwise>
        <xsl:text>12</xsl:text>
    </xsl:otherwise>
    </xsl:choose>
</xsl:when>

<!-- da gestire altri attributi -->
<!-- valore di default 100, in modo da lasciare il valore senza % -->
<!-- NB: si potrebbe usare width (come valore di default) -->
<xsl:otherwise>
    <xsl:text>100</xsl:text>
</xsl:otherwise>

</xsl:choose>

</xsl:template>

<!-- ******************************************************************************** -->
<!-- ******************************************************************************** -->
<!-- ******************************************************************************** -->
<!-- ******************************************************************************** -->
<!-- ************************** TEMPLATE ATTRIBUTI TRANSFORM ************************ -->
<!-- ******************************************************************************** -->
<!-- ******************************************************************************** -->
<!-- ******************************************************************************** -->
<!-- ******************************************************************************** -->

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: transform ric ************************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="transform-ric">
    <xsl:param name="stringa" />
    <xsl:param name="w" />
    <xsl:param name="h" />
    <xsl:param name="x"><xsl:text>0</xsl:text></xsl:param>
    <xsl:param name="y"><xsl:text>0</xsl:text></xsl:param>
    <xsl:param name="cs"><xsl:text>-1</xsl:text></xsl:param>
    
<!-- crea un gruppo per ogni elemento contenuto in transform e poi in base all'elemento 
     corrente richiama un opportuno template per gestire la trasformazione.
     Funziona ricorsivamente, di volta in volta estrae una sottostringa da transform,
     fino a quando non resta vuoto.
-->
    
<!-- note:
    - scale(0) in qualsiasi punto sia elimina tutto il transform
    - translate dopo scale deve essere trattato prima, non viene scalato
-->    
    
<!-- 
    - cerchiamo scale 0, se c'è ...
    - gestisco i translate rotate e scale 
    - non gestiti matrix skewx, skewy
-->

    <!-- contiene la stringa in cui sostituisco ai rotate con x,y 2 translate e un rotate,
         che rappresentano una rotazione equivalente -->         
    <xsl:variable name="stringa-norm">
        <xsl:call-template name="normalizza-transform">
            <xsl:with-param name="stringa">
                <xsl:value-of select="$stringa" />
            </xsl:with-param>
        </xsl:call-template>
    </xsl:variable>
    
    
    <xsl:choose>
        <xsl:when test="($stringa-norm != '') and 
                        (contains($stringa-norm, 'scale(0)')!= '1')">
            <!-- creo due variabili, una con la trasformazione da effettuare adesso,
                 una con quella da effettuare in seguito -->
            <xsl:variable name="tr-attuale">
                <xsl:value-of select="concat(substring-before($stringa-norm,')'),')')" />
            </xsl:variable>
            <xsl:variable name="tr-successiva">
                <xsl:value-of select="substring-after($stringa-norm,')')" />
            </xsl:variable>
            
            <v:group>
                <!-- chiamo un template per gestire la trasformazione corrente -->
                <xsl:call-template name="transform-group">
                    <xsl:with-param name="tipo">
                        <xsl:value-of select="$tr-attuale" />
                    </xsl:with-param>
                    <xsl:with-param name="w">
                        <xsl:value-of select="$w" />
                    </xsl:with-param>
                    <xsl:with-param name="h">
                        <xsl:value-of select="$h" />
                    </xsl:with-param>
                    <xsl:with-param name="x">
                        <xsl:value-of select="$x" />
                    </xsl:with-param>
                    <xsl:with-param name="y">
                        <xsl:value-of select="$y" />
                    </xsl:with-param>
                    <xsl:with-param name="cs">
                        <xsl:value-of select="$cs" />
                    </xsl:with-param>
                </xsl:call-template>
            
                <!-- modifico x,y,w,h in base alla trasformazione corrente:
                        questi parametri mi serviranno per gestire la
                        trasformazione successiva: vengono modificati solo
                        se la trasformazione è scale, in quanto le altre
                        non influenzano le trasformazioni successive. -->
                <xsl:variable name="nuova-w">
                    <xsl:call-template name="w-group-prec" >
                        <xsl:with-param name="tipo">
                            <xsl:value-of select="$tr-attuale" />
                        </xsl:with-param>
                        <xsl:with-param name="w">
                            <xsl:value-of select="$w" />
                        </xsl:with-param>
                    </xsl:call-template>
                </xsl:variable>
                
                <xsl:variable name="nuova-h">
                    <xsl:call-template name="h-group-prec" >
                        <xsl:with-param name="tipo">
                            <xsl:value-of select="$tr-attuale" />
                        </xsl:with-param>
                        <xsl:with-param name="h">
                            <xsl:value-of select="$h" />
                        </xsl:with-param>
                    </xsl:call-template>
                </xsl:variable>
                
                <xsl:variable name="nuova-x">
                    <xsl:call-template name="x-group-prec" >
                        <xsl:with-param name="tipo">
                            <xsl:value-of select="$tr-attuale" />
                        </xsl:with-param>
                        <xsl:with-param name="x">
                            <xsl:value-of select="$x" />
                        </xsl:with-param>
                    </xsl:call-template>
                </xsl:variable>
                
                <xsl:variable name="nuova-y">
                    <xsl:call-template name="y-group-prec" >
                        <xsl:with-param name="tipo">
                            <xsl:value-of select="$tr-attuale" />
                        </xsl:with-param>
                        <xsl:with-param name="y">
                            <xsl:value-of select="$y" />
                        </xsl:with-param>
                    </xsl:call-template>
                </xsl:variable>
            
                <!-- richiamo ricorsivamente questa funzione, passandogli
                        come parametro la stringa contenente le altre
                        trasformazioni da gestire e i valori modificati di
                        x,y,w,h -->
                <xsl:call-template name="transform-ric" >
                    <xsl:with-param name="stringa">
                        <xsl:value-of select="$tr-successiva" />
                    </xsl:with-param>
                    <xsl:with-param name="w">
                        <xsl:value-of select="$nuova-w" />
                    </xsl:with-param>
                    <xsl:with-param name="h">
                        <xsl:value-of select="$nuova-h" />
                    </xsl:with-param>
                    <xsl:with-param name="x">
                        <xsl:value-of select="$nuova-x" />
                    </xsl:with-param>
                    <xsl:with-param name="y">
                        <xsl:value-of select="$nuova-y" />
                    </xsl:with-param>
                </xsl:call-template>
                
            </v:group>
            
        </xsl:when>
        <!-- finito di gestire le trasformazione (e aver creato per ognuna un
                gruppo), devo inserire gli elementi all'interno di questi gruppi.
                Ricordo che ogni elemento con attributo transform chiama questa 
                funzione, da qui devo richiamare il template per la gestione 
                di quell'elemento, per sapere quale elemento era mi baso sul
                nome, quindi, devo gestire ogni possibile nome di elemento che 
                poteva aver chiamato questo template.
        -->
        <xsl:otherwise>
            <!-- vado per casi, devo considerare TUTTI gli elementi che possono avere
                 transform -->
            <xsl:choose>
                <xsl:when test="name() = 'g'">
                    <xsl:call-template name="svg-g" >
                        <xsl:with-param name="w">
                            <xsl:value-of select="$w" />
                        </xsl:with-param>
                        <xsl:with-param name="h">
                            <xsl:value-of select="$h" />
                        </xsl:with-param>
                        <xsl:with-param name="x">
                            <xsl:value-of select="$x" />
                        </xsl:with-param>
                        <xsl:with-param name="y">
                            <xsl:value-of select="$y" />
                        </xsl:with-param>
                    </xsl:call-template>
                </xsl:when>     
                <xsl:when test="name() = 'ellipse'">
                    <xsl:call-template name="svg-ellipse" />
                </xsl:when>
                <xsl:when test="name() = 'rect'">
                    <xsl:call-template name="svg-rect" />
                </xsl:when>
                <xsl:when test="name() = 'line'">
                    <xsl:call-template name="svg-line" />
                </xsl:when>
                <xsl:when test="name() = 'polyline'">
                    <xsl:call-template name="svg-polyline" />
                </xsl:when>
                <xsl:when test="name() = 'polygon'">
                    <xsl:call-template name="svg-polygon" />
                </xsl:when>
                <xsl:when test="name() = 'circle'">
                    <xsl:call-template name="svg-circle" />
                </xsl:when>    
                <xsl:when test="name() = 'image'">
                    <xsl:call-template name="inserisci-image" />
                </xsl:when>
                <xsl:when test="name() = 'path'">
                    <xsl:call-template name="svg-path" />
                </xsl:when>
                <xsl:when test="name() = 'use'">
                    <xsl:call-template name="svg-use">
                        <xsl:with-param name="w">
                            <xsl:value-of select="$w" />
                        </xsl:with-param>
                        <xsl:with-param name="h">
                            <xsl:value-of select="$h" />
                        </xsl:with-param>
                    </xsl:call-template>
                </xsl:when>
                <!-- forse anche qua ci vogliono i parametri -->
                <xsl:when test="name() = 'text'">
                    <xsl:call-template name="svg-text" />
                </xsl:when>                                            
            </xsl:choose>    
        </xsl:otherwise>
    </xsl:choose>
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: transform group  *********************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="transform-group" >
    <xsl:param name="tipo" />
    <xsl:param name="w" />
    <xsl:param name="h" />
    <xsl:param name="x" />
    <xsl:param name="y" />
    <xsl:param name="cs"><xsl:text>-1</xsl:text></xsl:param>
    
<!-- in base al tipo di trasformazione (parametro tipo) gestisco gli attributi del
      gruppo (creato in transform-ric) utilizzando i parametri passati in input -->    
 
    
<!-- vado per casi sul tipo di transformazione -->
<xsl:choose>
<!-- XXXXXXXXXXXXXXXXXXXX TRANSLATE XXXXXXXXXXXXXXXXXXX -->
<xsl:when test="normalize-space(substring-before
                (substring-after($tipo,'translate('),')')) !=''">
                
        <!-- per le traslazioni, i parametri w e h restano invariati,
             x,y, vengono modificati aggiungendo i valori di traslazione,
             opportunamente convertiti, e calcolati tramite i template
             translate-x-val e translate-y-val che estraggono e aggiustano
             i valori contenuti nella stringa (che rappresenta una porzione
             dell'attributo transform)
        -->        
        <xsl:attribute name="style">
            <xsl:text>position: absolute; </xsl:text>
            <xsl:text>left: </xsl:text>
                <xsl:variable name="tr-x">
                    <xsl:call-template name="translate-x-val">
                        <xsl:with-param name="stringa">
                            <xsl:value-of select="$tipo" />
                        </xsl:with-param>
                    </xsl:call-template>
                </xsl:variable>

                <xsl:value-of select="$tr-x + $x" />
                <xsl:text>; top: </xsl:text>
                <xsl:variable name="tr-y">
                    <xsl:call-template name="translate-y-val">
                        <xsl:with-param name="stringa">
                            <xsl:value-of select="$tipo" />
                        </xsl:with-param>
                    </xsl:call-template>
                </xsl:variable>

                <xsl:value-of select="$tr-y + $y" />
            <xsl:text>; </xsl:text>

            <xsl:text>width: </xsl:text>
            <xsl:value-of select="$w" />
            <xsl:text>; height: </xsl:text>
            <xsl:value-of select="$h" />
            
        </xsl:attribute>
        
        <xsl:if test="$cs != '-1'">
                <xsl:attribute name="coordsize">
                    <xsl:value-of select="$cs" />
                </xsl:attribute>
        </xsl:if>
                
</xsl:when>
<!-- XXXXXXXXXXXXXXXXXXXX SCALE XXXXXXXXXXXXXXXXXXX -->
<xsl:when test="normalize-space(substring-before
                (substring-after($tipo,'scale('),')')) !=''">

        <!-- x,y,w,h non vengono modificati, si modificano solo
             coordorigin e coordsize (tramite template is-scale) -->

        <xsl:attribute name="style">
            <xsl:text>position: absolute; </xsl:text>
            <xsl:text>left: </xsl:text>
                <xsl:value-of select="$x" />
            <xsl:text> ; top: </xsl:text>
                <xsl:value-of select="$y" />
            <xsl:text> ; </xsl:text>
            
            <xsl:text>width: </xsl:text>
                <xsl:value-of select="$w" />
            <xsl:text>; height: </xsl:text>
                <xsl:value-of select="$h" />
        </xsl:attribute>
        
        <xsl:call-template name="is-scale">
            <xsl:with-param name="stringa">
                <xsl:value-of select="$tipo" />
            </xsl:with-param>
            <xsl:with-param name="w-val">
                <xsl:value-of select="$w" />
            </xsl:with-param>
            <xsl:with-param name="h-val">
                <xsl:value-of select="$h" />
            </xsl:with-param>
            <xsl:with-param name="x-val">
                <xsl:value-of select="$x" />
            </xsl:with-param>
            <xsl:with-param name="y-val">
                <xsl:value-of select="$y" />
            </xsl:with-param>
        </xsl:call-template>
        
</xsl:when>
<!-- XXXXXXXXXXXXXXXXXXXX ROTATE XXXXXXXXXXXXXXXXXXX -->
<xsl:when test="normalize-space(substring-before
                (substring-after($tipo,'rotate('),')')) !=''">
    <!-- può avere 3 parametri (1, 2 o 3) -->
    <!-- <rotate-angle> [<cx> <cy>] -->
    <!-- i valori mancanti vengono considerati 0 -->
    
    <xsl:variable name="valori">
        <xsl:value-of select="normalize-space(
                substring-before(substring-after($tipo,'rotate('),')'))" />
    </xsl:variable>
    
    <xsl:variable name="angle">
        <xsl:variable name="angle-temp">
        <xsl:choose>
            <xsl:when test="contains($valori,' ')">
                <xsl:variable name="stringa-temp">
                    <xsl:value-of select="normalize-space(
                                substring-before($valori,' '))" />
                </xsl:variable>
                <xsl:choose>
                    <xsl:when test="contains($stringa-temp,',')">
                        <xsl:value-of select="normalize-space(
                            substring-before($stringa-temp,','))" />
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="$stringa-temp" />
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:when>
            <xsl:when test="contains($valori,',')">
                <xsl:value-of select="normalize-space(substring-before($valori,','))" />
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="$valori" />
            </xsl:otherwise>
        </xsl:choose>
        </xsl:variable>
        
        <!-- la gestione degli angoli tra svg e vml e divesa: per uno la rotazione è
              rispetto all'angolo in alto a sx della figura, per l'altro rispetto
              al centro. Per gestire questa differenza devo effettuare uno spostamento
              (tramite left e top), per sapere di quanto effettuare lo spostamento, ci 
              si è basati su funzioni geometriche, valide per angoli fino a 180 e fino a
              -180 gradi. Quindi si è dovuto aggiustare il valore dell'angolo per farlo 
              rientrare in questi parametri (nota che la modifica produce un angolo
              equivalente).-->
              
        <xsl:variable name="angolo-360">    
            <xsl:value-of select="$angle-temp mod 360" />
        </xsl:variable>
        
        <xsl:choose>
            <xsl:when test="$angolo-360 &gt; 180">
                <xsl:value-of select="- (360 - $angolo-360)" />
            </xsl:when>
            <xsl:when test="$angolo-360 &lt; -180">
                <xsl:value-of select="(360 + $angolo-360)" />
            </xsl:when>
            
            <xsl:otherwise>
                <xsl:value-of select="$angolo-360" />
            </xsl:otherwise>
        </xsl:choose>
    </xsl:variable>
    
    <xsl:variable name="rotate-x">
        <xsl:call-template name="secondo-valore">
            <xsl:with-param name="stringa">
                <xsl:value-of select="normalize-space($valori)" />
            </xsl:with-param>
            <xsl:with-param name="default">
                <xsl:text>0</xsl:text>
            </xsl:with-param>
        </xsl:call-template>
    </xsl:variable>

    <xsl:variable name="rotate-y">
            <xsl:call-template name="primo-valore">
            <xsl:with-param name="stringa">
                    <xsl:call-template name="dal-terzo-valore">
                        <xsl:with-param name="stringa">
                            <xsl:value-of select="normalize-space($valori)" />
                        </xsl:with-param>
                    </xsl:call-template>
            </xsl:with-param>
            <xsl:with-param name="default">
                <xsl:text>0</xsl:text>
            </xsl:with-param>
        </xsl:call-template>
    </xsl:variable>
    
    <xsl:attribute name="style">
            <xsl:text>position: absolute; </xsl:text>

            <!-- altezza dal punto piu' alto del rettangolo al centro del rettangolo -->
            <xsl:variable name="h1">
                <!-- h dal punto piu' alto a meta' dell'altezza -->
                <xsl:variable name="h-b1">
                    <xsl:variable name="coseno-h-b1">
                            <xsl:call-template name="cos-x">
                                <xsl:with-param name="x-val">
                                    <xsl:value-of select="$angle" />
                                </xsl:with-param>
                        </xsl:call-template>
                    </xsl:variable>
                    <xsl:value-of select="($h div 2) * $coseno-h-b1" />
                </xsl:variable>
                <!-- h da meta' dell'altezza al centro del rettangolo -->
                <xsl:variable name="h-b2">
                    <xsl:variable name="coseno-h-b2">
                            <xsl:call-template name="cos-x">
                                <xsl:with-param name="x-val">
                                    <xsl:value-of select="90 - $angle" />
                                </xsl:with-param>
                        </xsl:call-template>
                    </xsl:variable>
                    <xsl:value-of select="($w div 2) * $coseno-h-b2" />
                </xsl:variable>  
                <xsl:value-of select="$h-b1 + $h-b2" />              
            </xsl:variable>
            
            <!-- meta' altezza del rettangolo-->
            <xsl:variable name="h2">
                <xsl:value-of select="$h div 2" />
            </xsl:variable>
            
            <xsl:variable name="h-val">
                <xsl:value-of select="$h1 - $h2" />
            </xsl:variable>
            
            <!-- larghezza da meta' del rettangolo al punto piu' alto -->
            <xsl:variable name="w1">
                <!-- w da meta' del rettangolo a meta' dell'altezza -->
                <xsl:variable name="w-a1">
                    <xsl:variable name="coseno-w-a1">
                            <xsl:call-template name="cos-x">
                                <xsl:with-param name="x-val">
                                    <xsl:value-of select="$angle" />
                                </xsl:with-param>
                        </xsl:call-template>
                    </xsl:variable>
                    <xsl:value-of select="($w div 2) * $coseno-w-a1" />
                </xsl:variable>
                <!-- w dal punto piu' alto a meta' dell'altezza -->
                <xsl:variable name="w-a2">
                    <xsl:variable name="coseno-w-a2">
                            <xsl:call-template name="cos-x">
                                <xsl:with-param name="x-val">
                                    <xsl:value-of select="90 - $angle" />
                                </xsl:with-param>
                        </xsl:call-template>
                    </xsl:variable>
                    <xsl:value-of select="($h div 2) * $coseno-w-a2" />
                </xsl:variable>  
                <xsl:value-of select="$w-a1 - $w-a2" />              
            </xsl:variable>
            
            <!-- meta' larghezza del rettangolo-->
            <xsl:variable name="w2">
                <xsl:value-of select="$w div 2" />
            </xsl:variable>
            
            <xsl:variable name="w-val">
                <xsl:value-of select="$w2 - $w1" />
            </xsl:variable>
            
            <xsl:text>left: </xsl:text>
            <xsl:value-of select="$x - $w-val" />
            <xsl:text>; top: </xsl:text>
            <xsl:value-of select="$y + $h-val" />
            <xsl:text>; </xsl:text>
            
            <xsl:text>width: </xsl:text>
                    <xsl:value-of select="$w" />
            <xsl:text>; height: </xsl:text>
                    <xsl:value-of select="$h" />
            <xsl:text>; rotation: </xsl:text>
            <xsl:value-of select="$angle" />
            
            <xsl:text>;</xsl:text>
    </xsl:attribute>
    
    <xsl:if test="$cs != '-1'">
        <xsl:attribute name="coordsize">
            <xsl:value-of select="$cs" />
        </xsl:attribute>
    </xsl:if>
    
</xsl:when>
<!-- altri casi non gestiti -->
<!-- XXXXXXXXXXXXXXXXXXXX MATRIX XXXXXXXXXXXXXXXXXXX -->
<!-- XXXXXXXXXXXXXXXXXXXX SKEWX XXXXXXXXXXXXXXXXXXX -->
<!-- XXXXXXXXXXXXXXXXXXXX SKEWY XXXXXXXXXXXXXXXXXXX -->
<xsl:otherwise>
          <xsl:attribute name="style">
            <xsl:text>position: absolute; </xsl:text>
            <xsl:text>left: </xsl:text>
                <xsl:value-of select="$x" />
            <xsl:text>; top: </xsl:text>
                <xsl:value-of select="$y" />
            <xsl:text>; </xsl:text>
            <xsl:text>width: </xsl:text>
            <xsl:value-of select="$w" />
            <xsl:text>; height: </xsl:text>
            <xsl:value-of select="$h" />
        </xsl:attribute>
</xsl:otherwise>
</xsl:choose>
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ************************** TEMPLATE per TRANSLATE ****************************** -->
<!-- ******************************************************************************** -->

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: translate x val ************************************ -->
<!-- ******************************************************************************** -->
<xsl:template name="translate-x-val">
<xsl:param name="stringa"><xsl:text></xsl:text></xsl:param>
<!-- valore di translate della x -->

    <xsl:variable name="v-translate">
        <xsl:value-of select="normalize-space(substring-before
                (substring-after($stringa,'translate('),')'))" />
    </xsl:variable>
    
    <xsl:choose>
    <xsl:when test="$v-translate != ''">
    
        <!-- dobbiamo convertire in user unit, no, dobbiamo togliere l'eventuale
             unità di misura, svg lo interpreta come se non ci fosse!!!  -->
            <xsl:variable name="tx-temp">
                <xsl:choose>
                    <!-- siamo nella forma x1,y1 -->
                    <xsl:when test="substring-before($v-translate,',') != ''">
                            <xsl:value-of select="normalize-space(
                                substring-before($v-translate,','))" />
                    </xsl:when>
                    <!-- siamo nella forma x1 y1 -->
                    <xsl:when test="substring-before($v-translate,' ') != ''">
                            <xsl:value-of select="normalize-space(
                            substring-before($v-translate,' '))" />
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="$v-translate" />
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:variable>

            <xsl:value-of select="$tx-temp" />
    </xsl:when>
    <xsl:otherwise>
        <xsl:text>0</xsl:text>
    </xsl:otherwise>
    </xsl:choose>
    
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: translate y val ************************************ -->
<!-- ******************************************************************************** -->
<xsl:template name="translate-y-val">
<xsl:param name="stringa"><xsl:text></xsl:text></xsl:param>
<!-- valore di translate della y -->

    <xsl:variable name="v-translate">
        <xsl:value-of select="normalize-space(substring-before
                (substring-after($stringa,'translate('),')'))" />
    </xsl:variable>
   
    <xsl:choose>
    <xsl:when test="$v-translate != ''">
    
            <xsl:variable name="ty-temp">
                <xsl:choose>
                    <!-- siamo nella forma x1,y1 -->
                    <xsl:when test="substring-after($v-translate,',') != ''">
                            <xsl:value-of select="normalize-space(
                            substring-after($v-translate,','))" />
                    </xsl:when>
                    <!-- siamo nella forma x1 y1 -->
                    <xsl:when test="substring-after($v-translate,' ') != ''">
                            <xsl:value-of select="normalize-space(
                            substring-after($v-translate,' '))" />
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:text>0</xsl:text>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:variable>
            
            <xsl:value-of select="$ty-temp" />
    </xsl:when>
    <xsl:otherwise>
        <xsl:text>0</xsl:text>
    </xsl:otherwise>
    </xsl:choose>
</xsl:template>


<!-- ******************************************************************************** -->
<!-- ************************** TEMPLATE per SCALE ********************************** -->
<!-- ******************************************************************************** -->

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: is scale ******************************************* -->
<!-- ******************************************************************************** -->
<xsl:template name="is-scale">
<xsl:param name="stringa"><xsl:text></xsl:text></xsl:param>
<xsl:param name="x-val"><xsl:text>-1</xsl:text></xsl:param>
<xsl:param name="y-val"><xsl:text>-1</xsl:text></xsl:param>
<xsl:param name="w-val"><xsl:text>-1</xsl:text></xsl:param>
<xsl:param name="h-val"><xsl:text>-1</xsl:text></xsl:param>

<!-- se è presente l'attributo transform con scale, bisogna modificare coordsize e 
     coordorigin -->

   <xsl:variable name="v-scale">
        <xsl:value-of select="normalize-space(substring-before
                (substring-after($stringa,'scale('),')'))" />
    </xsl:variable>
    
    <xsl:if test="$v-scale != ''">
    
        <!-- nuove variabili: scale x e scale y 
        (y potrebbe non esserci, se non c'è è uguale e x) -->
        <xsl:variable name="scale-x">
            <xsl:call-template name="scale-x-val">
                <xsl:with-param name="stringa">
                    <xsl:value-of select="$stringa" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:variable>
        
        <xsl:variable name="scale-y">
            <xsl:call-template name="scale-y-val">
            <xsl:with-param name="stringa">
                    <xsl:value-of select="$stringa" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:variable>
    
            <!-- devo dividere il valore di width per il valore di scale-x -->
            <xsl:variable name="w-scale">
                <xsl:choose>
                <xsl:when test="$w-val = '-1'">
                    <xsl:call-template name="scale-w-val" >
                        <xsl:with-param name="scale-x">
                            <xsl:value-of select="$scale-x" />
                        </xsl:with-param>
                    </xsl:call-template>
                </xsl:when>
                <xsl:otherwise>
                     <xsl:choose>
                        <xsl:when test="$scale-x = 0">
                            <xsl:value-of select="$w-val" />
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:value-of select="$w-val div $scale-x" />
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:otherwise>
                </xsl:choose>
            </xsl:variable>
            
            <!-- devo dividere il valore di height per il valore di scale-y -->
            <xsl:variable name="h-scale">
                <xsl:choose>
                <xsl:when test="$h-val = '-1'">
                    <xsl:call-template name="scale-h-val" >
                        <xsl:with-param name="scale-y">
                            <xsl:value-of select="$scale-y" />
                        </xsl:with-param>
                    </xsl:call-template>
                </xsl:when>
                <xsl:otherwise>
                     <xsl:choose>
                        <xsl:when test="$scale-y = 0">
                            <xsl:value-of select="$h-val" />
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:value-of select="$h-val div $scale-y" />
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:otherwise>
                </xsl:choose>
            </xsl:variable>
            
            <!-- devo dividere il valore di x per il valore di scale-x -->
            <xsl:variable name="x-scale">
                <xsl:choose>
                <xsl:when test="$x-val = '-1'">
                    <xsl:text>0</xsl:text>
                </xsl:when>
                <xsl:otherwise>
                     <xsl:choose>
                        <xsl:when test="$scale-x = 0">
                            <xsl:value-of select="$x-val" />
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:value-of select="$x-val div $scale-x" />
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:otherwise>
                </xsl:choose>
            </xsl:variable>
            
            <!-- devo dividere il valore di y per il valore di scale-y -->
            <xsl:variable name="y-scale">
                <xsl:choose>
                <xsl:when test="$y-val = '-1'">
                    <xsl:text>0</xsl:text>
                </xsl:when>
                <xsl:otherwise>
                     <xsl:choose>
                        <xsl:when test="$scale-y = 0">
                            <xsl:value-of select="$y-val" />
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:value-of select="$y-val div $scale-y" />
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:otherwise>
                </xsl:choose>
            </xsl:variable>
    
        <xsl:attribute name="coordorigin">
            <xsl:value-of select="$x-scale" /><xsl:text> </xsl:text>
            <xsl:value-of select="$y-scale" />
        </xsl:attribute> 
    
        
        <xsl:attribute name="coordsize">
            <xsl:value-of select="round($w-scale * 100) div 100" /><xsl:text> </xsl:text>
            <xsl:value-of select="round($h-scale * 100) div 100" />
        </xsl:attribute>
          
    </xsl:if>

</xsl:template>  

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: scale w val **************************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="scale-w-val">
<xsl:param name="scale-x"><xsl:text>0</xsl:text></xsl:param>

    <xsl:for-each select="ancestor::svg:svg">
        <xsl:if test="position() = last()">
            <xsl:choose>
                <xsl:when test="@viewBox">
                    <xsl:choose>
                        <xsl:when test="$scale-x = 0">
                            <xsl:value-of select="substring-before(
                            substring-after(substring-after(
                            normalize-space(@viewBox),' '),' '),' ')" />
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:value-of select="substring-before(
                            substring-after(substring-after(normalize-space
                            (@viewBox),' '),' '),' ') div $scale-x" />
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:when>
                
                <xsl:otherwise>
                    <xsl:variable name="var1">
                        <xsl:call-template name="svg-width" />
                    </xsl:variable>
                    <xsl:choose>
                        <xsl:when test="$scale-x = 0">
                            <xsl:value-of select="$var1" />
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:value-of select="$var1 div $scale-x" />
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:if>
    </xsl:for-each>
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: scale h val **************************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="scale-h-val">
<xsl:param name="scale-y"><xsl:text>0</xsl:text></xsl:param>

    <xsl:for-each select="ancestor::svg:svg">
        <xsl:if test="position() = last()">
            <xsl:choose>
                <xsl:when test="@viewBox">
                    <xsl:choose>
                        <xsl:when test="$scale-y = 0">
                            <xsl:value-of select="substring-after(
                            substring-after(substring-after(
                            normalize-space(@viewBox),' '),' '),' ')" />
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:value-of select="substring-after(
                            substring-after(substring-after(normalize-space
                            (@viewBox),' '),' '),' ') div $scale-y" />
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:when>
                
                <xsl:otherwise>
                    <xsl:variable name="var2">
                        <xsl:call-template name="svg-height" />
                    </xsl:variable>
                    <xsl:choose>
                        <xsl:when test="$scale-y = 0">
                            <xsl:value-of select="$var2" />
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:value-of select="$var2 div $scale-y" />
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:if>
    </xsl:for-each>
</xsl:template>



<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: scale x val ************************************ -->
<!-- ******************************************************************************** -->
<xsl:template name="scale-x-val">
<xsl:param name="stringa"><xsl:text></xsl:text></xsl:param>
<!-- valore di scale della x -->

    <xsl:variable name="v-scale">
        <xsl:value-of select="normalize-space(substring-before
                (substring-after(substring-after($stringa,'scale'),'('),')'))" />
    </xsl:variable>
    
    <xsl:choose>
    <xsl:when test="$v-scale != ''">
    
        <xsl:choose>
                <!-- siamo nella forma x1,y1 -->
                <xsl:when test="substring-before($v-scale,',') != ''">
                    <xsl:value-of select="normalize-space(
                                substring-before($v-scale,','))" />
                </xsl:when>
                <!-- siamo nella forma x1 y1 -->
                <xsl:when test="substring-before($v-scale,' ') != ''">
                    <xsl:value-of select="normalize-space(
                                substring-before($v-scale,' '))" />
                </xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="$v-scale" />
                </xsl:otherwise>
        </xsl:choose>
           
    </xsl:when>
    <xsl:otherwise>
        <xsl:text>1</xsl:text>
    </xsl:otherwise>
    </xsl:choose>
    
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: scale y val ************************************ -->
<!-- ******************************************************************************** -->
<xsl:template name="scale-y-val">
<xsl:param name="stringa"><xsl:text></xsl:text></xsl:param>
<!-- valore di scale della y -->

    <xsl:variable name="v-scale">
        <xsl:value-of select="normalize-space(substring-before
                (substring-after(substring-after($stringa,'scale'),'('),')'))" />
    </xsl:variable>
    
    <xsl:choose>
    <xsl:when test="$v-scale != ''">
    
            <xsl:choose>
                <!-- siamo nella forma x1,y1 -->
                <xsl:when test="substring-after($v-scale,',') != ''">
                    <xsl:value-of select="normalize-space(
                                substring-after($v-scale,','))" />
                </xsl:when>
                <!-- siamo nella forma x1 y1 -->
                <xsl:when test="substring-after($v-scale,' ') != ''">
                    <xsl:value-of select="normalize-space(
                                substring-after($v-scale,' '))" />
                </xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="$v-scale" />
                </xsl:otherwise>
            </xsl:choose>
           
    </xsl:when>
    <xsl:otherwise>
        <xsl:text>1</xsl:text>
    </xsl:otherwise>
    </xsl:choose>
    
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ************************** TEMPLATE per ROTATE ********************************* -->
<!-- ******************************************************************************** -->


<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: normalizza-transform ******************************* -->
<!-- ******************************************************************************** -->
<xsl:template name="normalizza-transform">
    <xsl:param name="stringa" />
    
        <!-- sostituisce a tutti i rotate presenti in stringa 2 translate e un rotate che
         rappresentano rotazioni equivalenti:
          - la prima funzione gestisce tutti i rotate, effettuando la sostituzione con i
            due translate e un rotate, ma essendo una funzione ricorsiva ho bisogno di
            cambiare il nome di rotate, per quelli già gestiti, per riuscire a passare 
            ai rotate successivi. Quindi uso un nome di commodo e con la seconda funzione,
            ritraduco questi nuovi nomi in rotate. -->
         
         <xsl:variable name="rotate-modificato-1">
             <xsl:call-template name="modifica-rotate">
                    <xsl:with-param name="stringa">
                        <xsl:value-of select="$stringa" />
                    </xsl:with-param>
            </xsl:call-template>            
        </xsl:variable>
        <xsl:variable name="rotate-modificato-2">
             <xsl:call-template name="traduci-in-rotate">
                    <xsl:with-param name="stringa">
                        <xsl:value-of select="$rotate-modificato-1" />
                    </xsl:with-param>
            </xsl:call-template>
        </xsl:variable>    
        
        <xsl:value-of select="$rotate-modificato-2" />
    
</xsl:template>
    
<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: modifica-rotate ************************************ -->
<!-- ******************************************************************************** -->
<xsl:template name="modifica-rotate">
    <xsl:param name="stringa" />    
    <!-- sostituisce ogni rotate con potate e converti i rotate con 3 parametri in 2 
         translate e un rotate:
         utilizzo la sostituzione in potate, perchè essendo una funzione ricorsiva, se
         non cambio il nome di rotate, rimango in un loop infinito, mentre quello che 
         voglio fare è gestire un rotate e poi passare al successivo. Una volta gestiti
         tutti i rotate verrà chiamata una funzione che ritraduce potate in rotate  -->
    <!-- ricorsiva -->
    
    <xsl:choose>
        <xsl:when test="contains($stringa,'rotate')">
            <xsl:variable name="stringa-prec">
                <xsl:value-of select="substring-before($stringa,'rotate')" />
            </xsl:variable>
            <xsl:variable name="stringa-succ">
                <xsl:value-of select="substring-after(
                                      substring-after($stringa,'rotate'),')')" />
            </xsl:variable>
            <xsl:variable name="stringa-rotate">
                <xsl:variable name="inside">
                    <xsl:value-of select="substring-before(
                                      substring-after(
                                      substring-after($stringa,'rotate'),'('),')')" />
                </xsl:variable>
                <xsl:choose>
                    <!-- rotate con x (e y) -->
                    <xsl:when test="contains($inside,',') or 
                                    contains(normalize-space($inside),' ')">

                        <xsl:variable name="rotation-val">
                            <xsl:call-template name="primo-valore">
                                <xsl:with-param name="stringa">
                                    <xsl:value-of select="normalize-space(
                                                        $inside)" />
                                </xsl:with-param>
                            </xsl:call-template>
                        </xsl:variable>
                        
                        <xsl:variable name="x-val">                        
                            <xsl:call-template name="secondo-valore">
                                <xsl:with-param name="stringa">
                                    <xsl:value-of select="normalize-space($inside)" />
                                </xsl:with-param>
                            </xsl:call-template>
                        </xsl:variable>
                        
                        <xsl:variable name="y-val">
                            <xsl:call-template name="primo-valore">
                                <xsl:with-param name="stringa">
                                    <xsl:call-template name="dal-terzo-valore">
                                        <xsl:with-param name="stringa">
                                            <xsl:value-of select="
                                                    normalize-space($inside)" />
                                        </xsl:with-param>
                                    </xsl:call-template>
                                </xsl:with-param>
                            </xsl:call-template>
                        </xsl:variable>

                        <xsl:variable name="x-val-inv">
                                <xsl:value-of select="- $x-val" />
                        </xsl:variable>
                        <xsl:variable name="y-val-inv">
                                <xsl:value-of select="- $y-val" />
                        </xsl:variable>

                        
                        <xsl:variable name="translate1">
                            <xsl:value-of select="concat(' translate(',$x-val,',',
                                                         $y-val,') ')" />
                        </xsl:variable>
                        
                        <xsl:variable name="translate2">
                            <xsl:value-of select="concat(' translate(',$x-val-inv,',',
                                                         $y-val-inv,') ')" />
                            <xsl:text></xsl:text>
                        </xsl:variable>
                        
                        <xsl:variable name="new-rotate">
                            <xsl:value-of select="concat('potate(',$rotation-val,')')" />
                        </xsl:variable>
                        
                        
                        <xsl:choose>
                            <xsl:when test="($x-val = '0') and ($y-val = '0')">
                                <xsl:value-of select="$new-rotate" />
                            </xsl:when>
                            <xsl:otherwise>
                                <xsl:value-of select="concat($translate1,$new-rotate,
                                                             $translate2)" />
                            </xsl:otherwise>
                        </xsl:choose>

                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="concat('potate(',$inside,')')" />
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:variable>
            
            <xsl:call-template name="modifica-rotate">
                    <xsl:with-param name="stringa">
                        <xsl:value-of select="concat($stringa-prec,$stringa-rotate,
                                                     $stringa-succ)" />
                    </xsl:with-param>
            </xsl:call-template>           
        </xsl:when>
        <xsl:otherwise>
            <xsl:value-of select="$stringa" />
        </xsl:otherwise>
    </xsl:choose>
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: traduci-in-rotate ********************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="traduci-in-rotate">
    <xsl:param name="stringa" />    
    <!-- sostituisce ogni potate con rotate -->
    <!-- ricorsiva -->
    
    <xsl:choose>
        <xsl:when test="contains($stringa,'potate')">
            <xsl:call-template name="traduci-in-rotate">
                    <xsl:with-param name="stringa">
                        <xsl:value-of select="concat(substring-before($stringa,'potate'),
                                                    'rotate',
                                                     substring-after($stringa,'potate'))" />
                    </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
        <xsl:otherwise>
            <xsl:value-of select="$stringa" />
        </xsl:otherwise>
    </xsl:choose>
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ************************** altri TEMPLATE ************************************** -->
<!-- ******************************************************************************** -->

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: w group prec  ************************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="w-group-prec">
    <xsl:param name="tipo" />
    <xsl:param name="w" />
<!-- restituisce il valore di width del gruppo precedente (che corrisponderebbe ad una 
     trasformazione precedente). Se la trasformazione precedente era scale, non restituisce
     width ma il primo valore di coordsize
-->

<xsl:choose>
<!-- XXXXXXXXXXXXXXXXXXXX SCALE XXXXXXXXXXXXXXXXXXX -->
<xsl:when test="normalize-space(substring-before
                (substring-after($tipo,'scale('),')')) !=''">
        <xsl:variable name="scale-x">
            <xsl:call-template name="scale-x-val">
                <xsl:with-param name="stringa">
                    <xsl:value-of select="$tipo" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:variable>
           
        <xsl:choose>
            <xsl:when test="$scale-x = 0">
                <xsl:value-of select="$w" />
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="$w div $scale-x" />
            </xsl:otherwise>
        </xsl:choose>     

</xsl:when>
<xsl:otherwise>
    <xsl:value-of select="$w" />
</xsl:otherwise>
</xsl:choose>
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: h group prec  ************************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="h-group-prec">
    <xsl:param name="tipo" />
    <xsl:param name="h" />
<!-- restituisce il valore di height del gruppo precedente (che corrisponderebbe ad una 
     trasformazione precedente). Se la trasformazione precedente era scale, non restituisce
     height ma il secondo valore di coordsize
-->

<xsl:choose>
<!-- XXXXXXXXXXXXXXXXXXXX SCALE XXXXXXXXXXXXXXXXXXX -->
<xsl:when test="normalize-space(substring-before
                (substring-after($tipo,'scale('),')')) !=''">
        <xsl:variable name="scale-y">
            <xsl:call-template name="scale-y-val">
                <xsl:with-param name="stringa">
                    <xsl:value-of select="$tipo" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:variable>
        
        <xsl:choose>
            <xsl:when test="$scale-y = 0">
                <xsl:value-of select="$h" />
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="$h div $scale-y" />
            </xsl:otherwise>
        </xsl:choose>     
           
</xsl:when>
<xsl:otherwise>
    <xsl:value-of select="$h" />
</xsl:otherwise>
</xsl:choose>
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: x group prec  ************************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="x-group-prec">
    <xsl:param name="tipo" />
    <xsl:param name="x" />
<!-- restituisce il valore di x del gruppo precedente (che corrisponderebbe ad una 
     trasformazione precedente). Se la trasformazione precedente era scale, non restituisce
     x ma il primo valore di coordorigin
-->

<xsl:choose>
<!-- XXXXXXXXXXXXXXXXXXXX SCALE XXXXXXXXXXXXXXXXXXX -->
<xsl:when test="normalize-space(substring-before
                (substring-after($tipo,'scale('),')')) !=''">
        <xsl:variable name="scale-x">
            <xsl:call-template name="scale-x-val">
                <xsl:with-param name="stringa">
                    <xsl:value-of select="$tipo" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:variable>
           
        <xsl:choose>
            <xsl:when test="$scale-x = 0">
                <xsl:value-of select="$x" />
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="$x div $scale-x" />
            </xsl:otherwise>
        </xsl:choose>     

</xsl:when>
<xsl:otherwise>
    <xsl:value-of select="$x" />
</xsl:otherwise>
</xsl:choose>
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: y group prec  ************************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="y-group-prec">
    <xsl:param name="tipo" />
    <xsl:param name="y" />
<!-- restituisce il valore di y del gruppo precedente (che corrisponderebbe ad una 
     trasformazione precedente). Se la trasformazione precedente era scale, non restituisce
     y ma il primo valore di coordorigin
-->

<xsl:choose>
<!-- XXXXXXXXXXXXXXXXXXXX SCALE XXXXXXXXXXXXXXXXXXX -->
<xsl:when test="normalize-space(substring-before
                (substring-after($tipo,'scale('),')')) !=''">
        <xsl:variable name="scale-y">
            <xsl:call-template name="scale-y-val">
                <xsl:with-param name="stringa">
                    <xsl:value-of select="$tipo" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:variable>
           
        <xsl:choose>
            <xsl:when test="$scale-y = 0">
                <xsl:value-of select="$y" />
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="$y div $scale-y" />
            </xsl:otherwise>
        </xsl:choose>     

</xsl:when>
<xsl:otherwise>
    <xsl:value-of select="$y" />
</xsl:otherwise>
</xsl:choose>
</xsl:template>



<!-- Questi ultimi due template sono usati per simulare il comportamento di transform-ric,
     senza creare i vari gruppi, gestendo solo i valori dei parametri (x,y,w,h). Mi serve
     per sapere i valori dei parametri dovuti ad una data trasformazione (presente in un
     dato elemento) per gestire i valori di x,y,w,h di elementi group descending, in quanto
     per gestire questi valori devo sapere gli ultimi valori della trasformazione 
     precedente.
-->

<!-- ********************************************************************************* --> 
<!-- ********************************************************************************* -->
<!-- ***************** TEMPLATE: calcola-val-group-prec ****************************** -->
<!-- ********************************************************************************* -->
<!-- ********************************************************************************* -->
<xsl:template name="calcola-val-group-prec">
    <xsl:param name="attributo"><xsl:text>w</xsl:text></xsl:param>

    <!-- restituisce il valori di x,y,w,h, dell'attributo viewbox dell'ultimo elemento 
         ancestor che ha tale attributo, opportunamente modificato:
             - se è presente un gruppo ancestor con una trasformazione di tipo scale,
                chiamo un template (ricerca-attributo-group-ric) a cui passo i valori
                di x,y,w,h di cui ho bisogno, i quali verranno scalati in base al 
                valore di scale trovato.
             - se non è presente cerco l'ultimo elemento ancestor che ha viewbox ed
               estraggo l'opportuno valore.
    --> 
    <xsl:choose>
        <xsl:when test="$attributo = 'x'">
            <!-- chiamo la funzione ricorsiva: mi deve calcolare il
                  coordorigin dell'ultimo elemento group che ha scale -->
            <xsl:choose>
                <xsl:when test="ancestor::svg:g[contains(@transform,'scale')]">
                    <xsl:for-each select="ancestor::svg:g[contains(@transform,'scale')]">
                        <xsl:if test="position() = last()">
                            <xsl:call-template name="ricerca-attributo-group-ric">
                                <xsl:with-param name="stringa">
                                    <xsl:value-of select="@transform" />
                                </xsl:with-param>  
                                <xsl:with-param name="attributo">
                                    <xsl:text>x</xsl:text>
                                </xsl:with-param>
                                <xsl:with-param name="w">
                                    <xsl:call-template name="width-of-svg" />
                                </xsl:with-param>
                                <xsl:with-param name="h">
                                    <xsl:call-template name="height-of-svg" />
                                </xsl:with-param>
                                <xsl:with-param name="x">
                                    <xsl:call-template name="x-of-svg" />
                                </xsl:with-param>
                                <xsl:with-param name="y">
                                    <xsl:call-template name="y-of-svg" />
                                </xsl:with-param>
                            </xsl:call-template>
                        </xsl:if>
                    </xsl:for-each>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:call-template name="x-of-svg" />
                </xsl:otherwise>
            </xsl:choose>
        </xsl:when>
        <xsl:when test="$attributo = 'y'">
                <xsl:choose>
                <xsl:when test="ancestor::svg:g[contains(@transform,'scale')]">
                    <xsl:for-each select="ancestor::svg:g[contains(@transform,'scale')]">
                        <xsl:if test="position() = last()">
                            <xsl:call-template name="ricerca-attributo-group-ric">
                                <xsl:with-param name="stringa">
                                    <xsl:value-of select="@transform" />
                                </xsl:with-param>  
                                <xsl:with-param name="attributo">
                                    <xsl:text>y</xsl:text>
                                </xsl:with-param>
                                <xsl:with-param name="w">
                                    <xsl:call-template name="width-of-svg" />
                                </xsl:with-param>
                                <xsl:with-param name="h">
                                    <xsl:call-template name="height-of-svg" />
                                </xsl:with-param>
                                <xsl:with-param name="x">
                                    <xsl:call-template name="x-of-svg" />
                                </xsl:with-param>
                                <xsl:with-param name="y">
                                    <xsl:call-template name="y-of-svg" />
                                </xsl:with-param>
                            </xsl:call-template>
                        </xsl:if>
                    </xsl:for-each>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:call-template name="y-of-svg" />
                </xsl:otherwise>
            </xsl:choose>
        </xsl:when>
        <xsl:when test="$attributo = 'w'">
                <xsl:choose>
                <xsl:when test="ancestor::svg:g[contains(@transform,'scale')]">
                    <xsl:for-each select="ancestor::svg:g[contains(@transform,'scale')]">
                        <xsl:if test="position() = last()">
                            <xsl:call-template name="ricerca-attributo-group-ric">
                                <xsl:with-param name="stringa">
                                    <xsl:value-of select="@transform" />
                                </xsl:with-param>  
                                <xsl:with-param name="attributo">
                                    <xsl:text>w</xsl:text>
                                </xsl:with-param>
                                <xsl:with-param name="w">
                                    <xsl:call-template name="width-of-svg" />
                                </xsl:with-param>
                                <xsl:with-param name="h">
                                    <xsl:call-template name="height-of-svg" />
                                </xsl:with-param>
                                <xsl:with-param name="x">
                                    <xsl:call-template name="x-of-svg" />
                                </xsl:with-param>
                                <xsl:with-param name="y">
                                    <xsl:call-template name="y-of-svg" />
                                </xsl:with-param>
                            </xsl:call-template>
                        </xsl:if>
                    </xsl:for-each>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:call-template name="width-of-svg" />
                </xsl:otherwise>
            </xsl:choose>
        </xsl:when>
        <xsl:otherwise> <!-- h -->
                <xsl:choose>
                <xsl:when test="ancestor::svg:g[contains(@transform,'scale')]">
                    <xsl:for-each select="ancestor::svg:g[contains(@transform,'scale')]">
                        <xsl:if test="position() = last()">
                            <xsl:call-template name="ricerca-attributo-group-ric">
                                <xsl:with-param name="stringa">
                                    <xsl:value-of select="@transform" />
                                </xsl:with-param>  
                                <xsl:with-param name="attributo">
                                    <xsl:text>h</xsl:text>
                                </xsl:with-param>
                                <xsl:with-param name="w">
                                    <xsl:call-template name="width-of-svg" />
                                </xsl:with-param>
                                <xsl:with-param name="h">
                                    <xsl:call-template name="height-of-svg" />
                                </xsl:with-param>
                                <xsl:with-param name="x">
                                    <xsl:call-template name="x-of-svg" />
                                </xsl:with-param>
                                <xsl:with-param name="y">
                                    <xsl:call-template name="y-of-svg" />
                                </xsl:with-param>
                            </xsl:call-template>
                        </xsl:if>
                    </xsl:for-each>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:call-template name="height-of-svg" />
                </xsl:otherwise>
            </xsl:choose>
        </xsl:otherwise>
    </xsl:choose>
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: ricerca attributo group ric ************************ -->
<!-- ******************************************************************************** -->
<xsl:template name="ricerca-attributo-group-ric">
    <xsl:param name="stringa" />
    <xsl:param name="attributo"><xsl:text>w</xsl:text></xsl:param>
    <xsl:param name="valore"><xsl:text>0</xsl:text></xsl:param>
    <xsl:param name="w" />
    <xsl:param name="h" />
    <xsl:param name="x"><xsl:text>0</xsl:text></xsl:param>
    <xsl:param name="y"><xsl:text>0</xsl:text></xsl:param>
    
<!-- 
     Funziona ricorsivamente, di volta in volta estrae una sottostringa da transform,
     fino a quando non resta vuoto, considerando solo le trasformazioni di tipo scale 
     e modifica i valori passati in input in base ad ogni scale trovato.
-->
    

    <xsl:choose>
        <xsl:when test="($stringa != '') and contains($stringa, 'scale')">
            <!-- creo due variabili, una con la scala da effettuare adesso,
                 una con quella da effettuare in seguito -->
            <xsl:variable name="tr-attuale">
                <xsl:value-of select="concat('scale',substring-before(
                                             substring-after($stringa,'scale'),')'),')')" />
            </xsl:variable>
            <xsl:variable name="tr-successiva">
                <xsl:value-of select="substring-after(
                                      substring-after($stringa,'scale'),')')" />
            </xsl:variable>
            
                <!-- vengono calcolati i valori scaltati -->
                <xsl:variable name="nuova-w">
                    <xsl:call-template name="w-group-prec" >
                        <xsl:with-param name="tipo">
                            <xsl:value-of select="$tr-attuale" />
                        </xsl:with-param>
                        <xsl:with-param name="w">
                            <xsl:value-of select="$w" />
                        </xsl:with-param>
                    </xsl:call-template>
                </xsl:variable>
                
                <xsl:variable name="nuova-h">
                    <xsl:call-template name="h-group-prec" >
                        <xsl:with-param name="tipo">
                            <xsl:value-of select="$tr-attuale" />
                        </xsl:with-param>
                        <xsl:with-param name="h">
                            <xsl:value-of select="$h" />
                        </xsl:with-param>
                    </xsl:call-template>
                </xsl:variable>
                
                <xsl:variable name="nuova-x">
                    <xsl:call-template name="x-group-prec" >
                        <xsl:with-param name="tipo">
                            <xsl:value-of select="$tr-attuale" />
                        </xsl:with-param>
                        <xsl:with-param name="x">
                            <xsl:value-of select="$x" />
                        </xsl:with-param>
                    </xsl:call-template>
                </xsl:variable>
                
                <xsl:variable name="nuova-y">
                    <xsl:call-template name="y-group-prec" >
                        <xsl:with-param name="tipo">
                            <xsl:value-of select="$tr-attuale" />
                        </xsl:with-param>
                        <xsl:with-param name="y">
                            <xsl:value-of select="$y" />
                        </xsl:with-param>
                    </xsl:call-template>
                </xsl:variable>
                
                <xsl:call-template name="ricerca-attributo-group-ric" >
                    <xsl:with-param name="stringa">
                        <xsl:value-of select="$tr-successiva" />
                    </xsl:with-param>    
                    <xsl:with-param name="attributo">
                        <xsl:value-of select="$attributo" />
                    </xsl:with-param>
                    <xsl:with-param name="valore">
                        <xsl:value-of select="$valore" />
                    </xsl:with-param>
                    <xsl:with-param name="w">
                        <xsl:value-of select="$nuova-w" />
                    </xsl:with-param>
                    <xsl:with-param name="h">
                        <xsl:value-of select="$nuova-h" />
                    </xsl:with-param>
                    <xsl:with-param name="x">
                        <xsl:value-of select="$nuova-x" />
                    </xsl:with-param>
                    <xsl:with-param name="y">
                        <xsl:value-of select="$nuova-y" />
                    </xsl:with-param>
                </xsl:call-template>
        </xsl:when>
        <xsl:otherwise>
            <!-- restituisco il valore -->
            <xsl:choose>
                <xsl:when test="$attributo = 'w'">
                    <xsl:value-of select="$w" />
                </xsl:when>
                <xsl:when test="$attributo = 'h'">
                    <xsl:value-of select="$h" />
                </xsl:when>
                <xsl:when test="$attributo = 'x'">
                    <xsl:value-of select="$x" />
                </xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="$y" />
                </xsl:otherwise>                                
            </xsl:choose>
        
        </xsl:otherwise>
    </xsl:choose>
</xsl:template>


<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: calcola scala  ************************************* -->
<!-- ******************************************************************************** -->
<xsl:template name="calcola-scala">
    <xsl:param name="valore"><xsl:text>1</xsl:text></xsl:param>
<!-- calcola il valore di scale per tutti gli elementi precedenti all'elemento corrente 
     (considere i valori di scale delle x) -->
<!-- ricorsiva -->

<!-- usato per modificare i valori di stroke-width e font-size in base ai valori
        di scale degli elementi ancestor
-->

<xsl:variable name="valore-mod">
    <xsl:choose>
        <xsl:when test="contains(@transform,'scale')">
            <xsl:variable name="scale-corr">
                <xsl:call-template name="calcola-valore-scale">
                    <xsl:with-param name="stringa">
                        <xsl:value-of select="@transform" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:variable>
            <xsl:value-of select="$valore * $scale-corr" />
        </xsl:when>
        <xsl:otherwise>
            <xsl:value-of select="$valore" />
        </xsl:otherwise>
    </xsl:choose>
</xsl:variable>

<xsl:choose>
    <xsl:when test="ancestor::svg:*[contains(@transform,'scale')]">
        <xsl:for-each select="ancestor::svg:*[contains(@transform,'scale')]">
            <xsl:if test="position() = last()">
                <xsl:call-template name="calcola-scala">
                    <xsl:with-param name="valore">
                        <xsl:value-of select="$valore-mod" />
                    </xsl:with-param>
                </xsl:call-template>
            </xsl:if>
        </xsl:for-each>
    </xsl:when>
    <xsl:otherwise>
        <xsl:value-of select="$valore-mod" />
    </xsl:otherwise>
</xsl:choose>
</xsl:template>

<!-- ******************************************************************************** -->
<!-- ***************** TEMPLATE: calcola valore scale  ****************************** -->
<!-- ******************************************************************************** -->
<xsl:template name="calcola-valore-scale">
    <xsl:param name="valore"><xsl:text>1</xsl:text></xsl:param>
    <xsl:param name="stringa" />
<!-- calcola il valore di scale dell'attributo transform (se ci sono piu' valori di scale
     li moltiplica tra loro) -->
<!-- ricorsiva -->

<xsl:choose>
    <xsl:when test="contains($stringa,'scale')">
        <xsl:variable name="valore-scale">
            <xsl:value-of select="substring-before(
                                  substring-after( 
                                  substring-after($stringa,'scale'),'('),')')" />
        </xsl:variable>
        <xsl:variable name="new-valore">
            <xsl:call-template name="primo-valore">
                <xsl:with-param name="stringa">
                    <xsl:value-of select="$valore-scale" />
                </xsl:with-param>
            </xsl:call-template>
        </xsl:variable>
        
        <xsl:variable name="stringa-succ">
            <xsl:value-of select="substring-after(substring-after($stringa,'scale'),')')" />
        </xsl:variable>
        
        <xsl:call-template name="calcola-valore-scale">
            <xsl:with-param name="stringa">
                <xsl:value-of select="$stringa-succ" />
            </xsl:with-param>
            <xsl:with-param name="valore">
                <xsl:value-of select="$valore * $new-valore" />
            </xsl:with-param>
        </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
        <xsl:value-of select="$valore" />
    </xsl:otherwise> 
</xsl:choose>

</xsl:template>

</xsl:stylesheet>
