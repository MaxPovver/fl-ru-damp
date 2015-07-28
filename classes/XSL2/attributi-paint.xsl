<?xml version="1.0" encoding="ISO-8859-1"?>
<!-- edited with XML Spy v4.0 U (http://www.xmlspy.com) by LEGO (LEGO) -->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:svg="http://www.w3.org/2000/svg" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns="http://www.w3.org/1999/xhtml" xmlns:xlink="http://www.w3.org/1999/xlink">
	<!-- INDEX:
   * template:
        - attributi-paint
        - stroke
        - stroke-width
            - divisione-vb            
            - valore-divisione-con-viewbox
        - stroke-linecap
        - stroke-linejoin
        - stroke-miterlimit
        - stroke-dasharray
        - stroke-dashoffset
        - stroke-opacity
        - stroke-opacity-ric
        - fill
        - fill-opacity
        - fill-opacity-ric
        - fill-rule
        - opacity-ric
-->
	<!-- gradient, per il momento lo impostiamo solo in fill e stroke (non so se va in altri
     elementi) -->
	<!-- ******************************************************************************** -->
	<!-- ***************** TEMPLATE: attributi paint ************************************ -->
	<!-- ******************************************************************************** -->
	<xsl:template name="attributi-paint">
		<!-- gestione di tutti gli attributi che riguardano proprietà di fill e stroke.
     NB: viene forzata la ricerca nei vari attributi o elementi style a cui ci
     si può riferire (in base al nome dell'elemento o agli attributi id e class)
-->
		<!-- NON GESTITI:  color, color-interpolation, color-rendering -->
		<!-- ****************************************** -->
		<v:stroke>
			<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX STROKE XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<xsl:call-template name="stroke"/>
			<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX STROKE-WIDTH XXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<xsl:variable name="width-temp">
				<xsl:call-template name="stroke-width"/>
			</xsl:variable>
			<xsl:choose>
				<xsl:when test="$width-temp = ''"/>
				<xsl:otherwise>
					<xsl:attribute name="weight"><xsl:call-template name="stroke-width"/></xsl:attribute>
				</xsl:otherwise>
			</xsl:choose>
			<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX STROKE-LINECAP XXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<xsl:call-template name="stroke-linecap"/>
			<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX STROKE-LINEJOIN XXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<xsl:call-template name="stroke-linejoin"/>
			<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX STROKE-MITERLIMIT XXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<xsl:call-template name="stroke-miterlimit"/>
			<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX STROKE-DASHARRAY XXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<xsl:call-template name="stroke-dasharray"/>
			<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX STROKE-DASHOFFSET XXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<xsl:call-template name="stroke-dashoffset"/>
			<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX STROKE-OPACITY XXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<xsl:attribute name="opacity"><xsl:call-template name="stroke-opacity"/></xsl:attribute>
		</v:stroke>
		<v:fill>
			<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX FILL XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<xsl:call-template name="fill"/>
			<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX FILL-RULE XXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<!-- valori: nonzero e evenodd.
     Con vml di default fa evenodd.
        Nonzero: supportato da vml ??
-->
			<xsl:call-template name="fill-rule"/>
			<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX FILL-OPACITY XXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<xsl:attribute name="opacity"><xsl:call-template name="fill-opacity"/></xsl:attribute>
		</v:fill>
	</xsl:template>
	<!-- ******************************************************************************** -->
	<!-- ***************** TEMPLATE: stroke ********************************************* -->
	<!-- ******************************************************************************** -->
	<xsl:template name="stroke">
		<!-- cerca stroke nell'elemento, se non lo trova lo cerca nel padre e nei nodi precedenti
     fino ad arrivare a svg, se non lo trova lo imposta col valore di 
     default (none, cioè on=false)
     Cerca anche negli elementi/attributi style
-->
		<!-- serve per scegliere quale style applicare, se quello associato all'id, a class o 
     all'elemento, oppure nessuno dei 3:
     mi restituisce una stringa che può iniziare con i,c,e o n a segnalare se
     devo cercare stroke all'interno di un qualche elemento style associato
     all'id, a class, al nome dell'elemento oppure se non c'è nessun riferimento ad
     elementi style.
     Nel caso che l'elemento corrente contenga l'attributo style (con al suo interno
      la proprietà di stroke), gli verrà data priorita, escludendo (cioè non considerando)
      gli altri riferimenti; la gestione è molto semplice, se trovo l'attributo style con
      all'interno un selettore per stroke, allora ignora questa variabile.
     NB: la gestione di proprietà contenute in style è fatta tramite il template
         ricerca-style e altri template contenuti nel file gestione-style.xsl -->
		<xsl:variable name="style-type">
			<xsl:call-template name="var-style-type">
				<xsl:with-param name="attributo">
					<xsl:text>stroke</xsl:text>
				</xsl:with-param>
			</xsl:call-template>
		</xsl:variable>
		<xsl:choose>
			<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<!-- XXXXXXXXXXXXXXXXXXXXXXX GESTIONE STYLE XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<xsl:when test="(@style and (contains(@style, 'stroke ') or contains(@style, 'stroke:'))) or
                (@id and substring($style-type,1,1) = 'i') or
                (@class and substring($style-type,1,1) = 'c') or
                (substring($style-type,1,1) = 'e')">
				<xsl:call-template name="ricerca-style">
					<xsl:with-param name="attributo">
						<xsl:text>stroke</xsl:text>
					</xsl:with-param>
					<xsl:with-param name="style-type">
						<xsl:value-of select="$style-type"/>
					</xsl:with-param>
				</xsl:call-template>
			</xsl:when>
			<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<!-- XXXXXXXXXXXXXXXXXXXXXXX ATTRIBUTO STROKE XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<xsl:when test="@stroke">
				<xsl:choose>
					<xsl:when test="substring(@stroke,1,3) = 'url'">
						<xsl:variable name="nome-el">
							<xsl:value-of select="substring(@stroke,6,string-length(@stroke) - 6)"/>
						</xsl:variable>
						<xsl:attribute name="on"><xsl:text>true</xsl:text></xsl:attribute>
						<!-- stroke di vml non supporta gradient!!!! -->
						<xsl:call-template name="gradient-pattern-ref">
							<xsl:with-param name="nome">
								<xsl:value-of select="$nome-el"/>
							</xsl:with-param>
							<xsl:with-param name="attributo">
								<xsl:text>stroke</xsl:text>
							</xsl:with-param>
						</xsl:call-template>
					</xsl:when>
					<xsl:when test="@stroke != 'none'">
						<xsl:attribute name="on"><xsl:text>true</xsl:text></xsl:attribute>
						<xsl:attribute name="color"><xsl:value-of select="@stroke"/></xsl:attribute>
					</xsl:when>
					<xsl:otherwise>
						<xsl:attribute name="on"><xsl:text>false</xsl:text></xsl:attribute>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:when>
			<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<!-- XXXXXXXXXXXXXXXXXXXXXXX RICORSIONE XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<!-- se non trovo stroke, lo cerco nel padre -->
			<!-- Se il padre è svg e non ha stroke, allora: none  -->
			<xsl:otherwise>
				<xsl:choose>
					<xsl:when test="name() = 'svg'">
						<xsl:choose>
							<xsl:when test="@stroke">
								<xsl:attribute name="on"><xsl:text>true</xsl:text></xsl:attribute>
								<xsl:attribute name="color"><xsl:value-of select="@stroke"/></xsl:attribute>
							</xsl:when>
							<xsl:otherwise>
								<!-- STROKE, default: none -->
								<xsl:attribute name="on"><xsl:text>false</xsl:text></xsl:attribute>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:when>
					<xsl:otherwise>
						<xsl:for-each select="..">
							<!-- se l'elemento è figlio di defs, e siamo arrivati a defs, lasciamo
                     non impostato l'attributo stroke, potrebbe essere definito
                     nell'elemento use
                -->
							<xsl:choose>
								<xsl:when test="name() = 'defs'"/>
								<xsl:otherwise>
									<xsl:call-template name="stroke"/>
								</xsl:otherwise>
							</xsl:choose>
						</xsl:for-each>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	<!-- ******************************************************************************** -->
	<!-- ***************** TEMPLATE: stroke-width *************************************** -->
	<!-- ******************************************************************************** -->
	<xsl:template name="stroke-width">
		<!-- cerca stroke-width nell'elemento, se non lo trova lo cerca nel padre e nei nodi 
     precedenti fino ad arrivare a svg, se non lo trova restituisce la stringa vuota, in
     modo che non venga impostata la dimensione.
-->
		<!-- SVG e VML hanno una diversa gestione di width: 
    - svg per la rappresentare la dimensione dei brodi, prende il valore di stroke-width  e
        lo modifica in base ai valori di w,h e viewbox degli elementi ancestor ed eventuali
        trasformazioni (scale), così come fa con tutti gli altri attributi.
    - vml d'altro canto, si comporta come svg per quasi tutti gli attributi, mentre per
        stroke-width, considera il valore in modo assoluto, cioè non viene 'aggiustato'
        in base ai dimensionamenti degli elementi ancestor, quindi bisgona forzare questo
        aggiustamento, dividendo opportunamente il valore (in base a w,h e viewbox) degli
        elementi ancestor e moltiplicandolo per il valore di eventuali trasformazioni di
        tipo scale.
-->
		<xsl:variable name="divisione">
			<xsl:call-template name="divisione-vb"/>
		</xsl:variable>
		<xsl:variable name="valore-scala">
			<xsl:call-template name="calcola-scala"/>
		</xsl:variable>
		<!-- seve per scegliere quale style applicare, se quello associato all'id, a class o 
     all'elemento, oppure nessuno dei 3 -->
		<xsl:variable name="style-type">
			<xsl:call-template name="var-style-type">
				<xsl:with-param name="attributo">
					<xsl:text>stroke-width</xsl:text>
				</xsl:with-param>
			</xsl:call-template>
		</xsl:variable>
		<xsl:choose>
			<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<!-- XXXXXXXXXXXXXXXXXXXXXXX GESTIONE STYLE XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<xsl:when test="(@style and (contains(@style, 'stroke-width ') or 
                contains(@style, 'stroke-width:'))) or
                (@id and substring($style-type,1,1) = 'i') or
                (@class and substring($style-type,1,1) = 'c') or
                (substring($style-type,1,1) = 'e')">
				<xsl:variable name="stroke-temp">
					<xsl:call-template name="ricerca-style">
						<xsl:with-param name="attributo">
							<xsl:text>stroke-width</xsl:text>
						</xsl:with-param>
						<xsl:with-param name="style-type">
							<xsl:value-of select="$style-type"/>
						</xsl:with-param>
					</xsl:call-template>
				</xsl:variable>
				<xsl:value-of select="($stroke-temp div $divisione) * $valore-scala"/>
			</xsl:when>
			<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<!-- XXXXXXXXXXXXXXXXXXXXXXX ATTRIBUTO STROKE-WIDTH XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<xsl:when test="@stroke-width">
				<xsl:variable name="stroke-temp">
					<xsl:call-template name="conversione">
						<xsl:with-param name="attributo">
							<xsl:value-of select="@stroke-width"/>
						</xsl:with-param>
						<xsl:with-param name="nome">
							<xsl:text>stroke-width</xsl:text>
						</xsl:with-param>
					</xsl:call-template>
				</xsl:variable>
				<xsl:value-of select="($stroke-temp div $divisione) * $valore-scala"/>
			</xsl:when>
			<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<!-- XXXXXXXXXXXXXXXXXXXXXXX RICORSIONE XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<xsl:otherwise>
				<xsl:choose>
					<xsl:when test="name() = 'svg'">
						<xsl:choose>
							<xsl:when test="@stroke-width">
								<!-- caso gestito in precedenza -->
							</xsl:when>
							<xsl:otherwise>
								<xsl:text/>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:when>
					<xsl:otherwise>
						<xsl:for-each select="..">
							<xsl:call-template name="stroke-width"/>
						</xsl:for-each>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	<!-- ******************************************************************************** -->
	<!-- ***************** TEMPLATE: divisione vb  ************************************** -->
	<!-- ******************************************************************************** -->
	<xsl:template name="divisione-vb">
		<xsl:param name="valore">
			<xsl:text>1</xsl:text>
		</xsl:param>
		<xsl:param name="wh">
			<xsl:text>si</xsl:text>
		</xsl:param>
		<!-- se e' no considera solo w e x -->
		<!-- funzione ricorsiva calcola il valore di width / viewbox(x) per ogni elemento ancestor
     dell'elemento corrente.
     Anche h/vb(y) poi li somma e fa div 2
-->
		<xsl:variable name="val-temp">
			<xsl:choose>
				<xsl:when test="@viewBox">
					<xsl:call-template name="valore-divisione-con-viewbox">
						<xsl:with-param name="wh">
							<xsl:value-of select="$wh"/>
						</xsl:with-param>
					</xsl:call-template>
				</xsl:when>
				<xsl:otherwise>
					<xsl:text>1</xsl:text>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>
		<xsl:choose>
			<xsl:when test="ancestor::*">
				<xsl:for-each select="ancestor::*">
					<xsl:if test="position() = last()">
						<xsl:call-template name="divisione-vb">
							<xsl:with-param name="valore">
								<xsl:value-of select="$valore * $val-temp"/>
							</xsl:with-param>
							<xsl:with-param name="wh">
								<xsl:value-of select="$wh"/>
							</xsl:with-param>
						</xsl:call-template>
					</xsl:if>
				</xsl:for-each>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$valore * $val-temp"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	<!-- ******************************************************************************** -->
	<!-- ***************** TEMPLATE: valore divisione con viewbox *********************** -->
	<!-- ******************************************************************************** -->
	<xsl:template name="valore-divisione-con-viewbox">
		<xsl:param name="wh">
			<xsl:text>si</xsl:text>
		</xsl:param>
		<!-- se e' no considera solo w e x -->
		<!-- per gestire stroke-width servono entrambi, nella gestione della dimensione 
            dei testi serve solo w e x. -->
		<!-- calcola il valore della divisione per stroke-width per gli elementi che hanno
     viewbox o come attributo proprio o negli ancestor -->
		<!-- richiamato all'interno di un elemento con viewBox -->
		<!-- il valore di stroke-width viene diviso per il valore di
             viewBox (di width, il 3° valore) moltiplicato per il valore di
             width dello stesso elemento (tradotto in user unit).
             Se non è presente viene considerato dimensione SCHERMO-X.
             Il valore di stroke-width verrà espresso con l'unità di misura
             di width!
             
        !!!! Si fa la stessa cosa per height, si sommano i due e si divide il valore
             per 2
        -->
		<xsl:variable name="viewbox-w">
			<xsl:value-of select="substring-before(substring-after
                (substring-after(normalize-space(@viewBox),' '),' '),' ')"/>
		</xsl:variable>
		<xsl:variable name="viewbox-h">
			<xsl:value-of select="substring-after(substring-after
            (substring-after(normalize-space(@viewBox),' '),' '),' ')"/>
		</xsl:variable>
		<xsl:variable name="valore-w">
			<xsl:choose>
				<xsl:when test="@width">
					<xsl:variable name="w">
						<xsl:call-template name="conversione">
							<xsl:with-param name="attributo">
								<xsl:value-of select="@width"/>
							</xsl:with-param>
							<xsl:with-param name="nome">
								<xsl:text>width</xsl:text>
							</xsl:with-param>
						</xsl:call-template>
					</xsl:variable>
					<xsl:value-of select="$viewbox-w div $w"/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="$viewbox-w div $schermo-x"/>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>
		<xsl:variable name="valore-h">
			<xsl:choose>
				<xsl:when test="@height">
					<xsl:variable name="h">
						<xsl:call-template name="conversione">
							<xsl:with-param name="attributo">
								<xsl:value-of select="@height"/>
							</xsl:with-param>
							<xsl:with-param name="nome">
								<xsl:text>height</xsl:text>
							</xsl:with-param>
						</xsl:call-template>
					</xsl:variable>
					<xsl:value-of select="$viewbox-h div $h"/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="$viewbox-h div $schermo-y"/>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>
		<xsl:choose>
			<xsl:when test="$wh = 'si'">
				<xsl:value-of select="($valore-w + $valore-h)  div 2"/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$valore-w"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	<!-- ******************************************************************************** -->
	<!-- ***************** TEMPLATE: stroke-linecap ************************************* -->
	<!-- ******************************************************************************** -->
	<xsl:template name="stroke-linecap">
		<!-- cerca stroke-linecap nell'elemento, se non lo trova lo cerca nel padre e nei nodi 
     precedenti fino ad arrivare a svg, se non lo trova lo imposta col valore di 
     default (flat)
-->
		<!-- il valore di default di svg è flat, quello di vml è round:
     di default va messo quello di svg!
-->
		<!-- serve per scegliere quale style applicare, se quello associato all'id, a class o 
     all'elemento, oppure nessuno dei 3 -->
		<xsl:variable name="style-type">
			<xsl:call-template name="var-style-type">
				<xsl:with-param name="attributo">
					<xsl:text>stroke-linecap</xsl:text>
				</xsl:with-param>
			</xsl:call-template>
		</xsl:variable>
		<xsl:choose>
			<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<!-- XXXXXXXXXXXXXXXXXXXXXXX GESTIONE STYLE XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<xsl:when test="(@style and (contains(@style, 'stroke-linecap ') or 
                             contains(@style, 'stroke-linecap:'))) or
                (@id and substring($style-type,1,1) = 'i') or
                (@class and substring($style-type,1,1) = 'c') or
                (substring($style-type,1,1) = 'e')">
				<xsl:call-template name="ricerca-style">
					<xsl:with-param name="attributo">
						<xsl:text>stroke-linecap</xsl:text>
					</xsl:with-param>
					<xsl:with-param name="style-type">
						<xsl:value-of select="$style-type"/>
					</xsl:with-param>
				</xsl:call-template>
			</xsl:when>
			<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<!-- XXXXXXXXXXXXXXXXXXXXXXX ATTRIBUTO STROKE - LINECAP XXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<xsl:when test="@stroke-linecap">
				<xsl:attribute name="endcap"><xsl:choose><xsl:when test="@stroke-linecap = 'butt'"><xsl:text>flat</xsl:text></xsl:when><xsl:otherwise><xsl:value-of select="@stroke-linecap"/></xsl:otherwise></xsl:choose></xsl:attribute>
			</xsl:when>
			<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<!-- XXXXXXXXXXXXXXXXXXXXXXX RICORSIONE XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<!-- se non trovo stroke-linecap, lo cerco nel padre -->
			<!-- Se il padre è svg e non ha linecap, allora: flat  -->
			<xsl:otherwise>
				<xsl:choose>
					<xsl:when test="name() = 'svg'">
						<xsl:choose>
							<xsl:when test="@stroke-linecap">
								<xsl:attribute name="endcap"><xsl:choose><xsl:when test="@stroke-linecap = 'butt'"><xsl:text>flat</xsl:text></xsl:when><xsl:otherwise><xsl:value-of select="@stroke-linecap"/></xsl:otherwise></xsl:choose></xsl:attribute>
							</xsl:when>
							<xsl:otherwise>
								<xsl:attribute name="endcap"><xsl:text>flat</xsl:text></xsl:attribute>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:when>
					<xsl:otherwise>
						<xsl:for-each select="..">
							<xsl:call-template name="stroke-linecap"/>
						</xsl:for-each>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	<!-- ******************************************************************************** -->
	<!-- ***************** TEMPLATE: stroke-linejoin ************************************* -->
	<!-- ******************************************************************************** -->
	<xsl:template name="stroke-linejoin">
		<!-- cerca stroke-linejoin nell'elemento, se non lo trova lo cerca nel padre e nei nodi 
     precedenti fino ad arrivare a svg, se non lo trova lo imposta col valore di 
     default (miter)
-->
		<!-- il valore di default di svg è miter, quello di vml è round:
     di default va messo quello di svg!
-->
		<!-- serve per scegliere quale style applicare, se quello associato all'id, a class o 
     all'elemento, oppure nessuno dei 3 -->
		<xsl:variable name="style-type">
			<xsl:call-template name="var-style-type">
				<xsl:with-param name="attributo">
					<xsl:text>stroke-linejoin</xsl:text>
				</xsl:with-param>
			</xsl:call-template>
		</xsl:variable>
		<xsl:choose>
			<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<!-- XXXXXXXXXXXXXXXXXXXXXXX GESTIONE STYLE XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<xsl:when test="(@style and (contains(@style, 'stroke-linejoin ') or 
                             contains(@style, 'stroke-linejoin:'))) or
                (@id and substring($style-type,1,1) = 'i') or
                (@class and substring($style-type,1,1) = 'c') or
                (substring($style-type,1,1) = 'e')">
				<xsl:call-template name="ricerca-style">
					<xsl:with-param name="attributo">
						<xsl:text>stroke-linejoin</xsl:text>
					</xsl:with-param>
					<xsl:with-param name="style-type">
						<xsl:value-of select="$style-type"/>
					</xsl:with-param>
				</xsl:call-template>
			</xsl:when>
			<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<!-- XXXXXXXXXXXXXXXXXXXXXXX ATTRIBUTO STROKE - LINEJOIN XXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<xsl:when test="@stroke-linejoin">
				<xsl:attribute name="joinstyle"><xsl:value-of select="@stroke-linejoin"/></xsl:attribute>
			</xsl:when>
			<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<!-- XXXXXXXXXXXXXXXXXXXXXXX RICORSIONE XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<!-- se non trovo stroke-linejoin, lo cerco nel padre -->
			<!-- Se il padre è svg e non ha linejoin, allora: miter  -->
			<xsl:otherwise>
				<xsl:choose>
					<xsl:when test="name() = 'svg'">
						<xsl:choose>
							<xsl:when test="@stroke-linejoin">
								<xsl:attribute name="joinstyle"><xsl:value-of select="@stroke-linejoin"/></xsl:attribute>
							</xsl:when>
							<xsl:otherwise>
								<xsl:attribute name="joinstyle"><xsl:text>miter</xsl:text></xsl:attribute>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:when>
					<xsl:otherwise>
						<xsl:for-each select="..">
							<xsl:call-template name="stroke-linejoin"/>
						</xsl:for-each>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	<!-- ******************************************************************************** -->
	<!-- ***************** TEMPLATE: stroke-miterlimit ************************************* -->
	<!-- ******************************************************************************** -->
	<xsl:template name="stroke-miterlimit">
		<!-- cerca stroke-miterlimit nell'elemento, se non lo trova lo cerca nel padre e nei nodi 
     precedenti fino ad arrivare a svg, se non lo trova lo imposta col valore di 
     default (4)
-->
		<!-- il valore di default di svg è 4, quello di vml è 8:
     di default va messo quello di svg!
-->
		<!-- serve per scegliere quale style applicare, se quello associato all'id, a class o 
     all'elemento, oppure nessuno dei 3 -->
		<xsl:variable name="style-type">
			<xsl:call-template name="var-style-type">
				<xsl:with-param name="attributo">
					<xsl:text>stroke-miterlimit</xsl:text>
				</xsl:with-param>
			</xsl:call-template>
		</xsl:variable>
		<xsl:choose>
			<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<!-- XXXXXXXXXXXXXXXXXXXXXXX GESTIONE STYLE XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<xsl:when test="(@style and (contains(@style, 'stroke-miterlimit ') or 
                             contains(@style, 'stroke-miterlimit:'))) or
                (@id and substring($style-type,1,1) = 'i') or
                (@class and substring($style-type,1,1) = 'c') or
                (substring($style-type,1,1) = 'e')">
				<xsl:call-template name="ricerca-style">
					<xsl:with-param name="attributo">
						<xsl:text>stroke-miterlimit</xsl:text>
					</xsl:with-param>
					<xsl:with-param name="style-type">
						<xsl:value-of select="$style-type"/>
					</xsl:with-param>
				</xsl:call-template>
			</xsl:when>
			<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<!-- XXXXXXXXXXXXXXXXXXXXXXX ATTRIBUTO STROKE - MITERLIMIT XXXXXXXXXXXXXXXXXXXXXXXXX -->
			<xsl:when test="@stroke-miterlimit">
				<xsl:attribute name="miterlimit"><xsl:value-of select="@stroke-miterlimit"/></xsl:attribute>
			</xsl:when>
			<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<!-- XXXXXXXXXXXXXXXXXXXXXXX RICORSIONE XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<!-- se non trovo stroke-miterlimit, lo cerco nel padre -->
			<!-- Se il padre è svg e non ha miterlimit, allora: 4  -->
			<xsl:otherwise>
				<xsl:choose>
					<xsl:when test="name() = 'svg'">
						<xsl:choose>
							<xsl:when test="@stroke-miterlimit">
								<xsl:attribute name="miterlimit"><xsl:value-of select="@stroke-miterlimit"/></xsl:attribute>
							</xsl:when>
							<xsl:otherwise>
								<xsl:attribute name="miterlimit"><xsl:text>4</xsl:text></xsl:attribute>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:when>
					<xsl:otherwise>
						<xsl:for-each select="..">
							<xsl:call-template name="stroke-miterlimit"/>
						</xsl:for-each>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	<!-- ******************************************************************************** -->
	<!-- ***************** TEMPLATE: stroke-dasharray *********************************** -->
	<!-- ******************************************************************************** -->
	<xsl:template name="stroke-dasharray">
		<!-- cerca stroke-dasharray nell'elemento, se non lo trova lo cerca nel padre e nei nodi 
     precedenti fino ad arrivare a svg, se non lo trova lo imposta col valore di 
     default (none)
-->
		<!-- il valore di default di svg è none, quello di vml è solid: solid -->
		<!-- serve per scegliere quale style applicare, se quello associato all'id, a class o 
     all'elemento, oppure nessuno dei 3 -->
		<xsl:variable name="style-type">
			<xsl:call-template name="var-style-type">
				<xsl:with-param name="attributo">
					<xsl:text>stroke-dasharray</xsl:text>
				</xsl:with-param>
			</xsl:call-template>
		</xsl:variable>
		<xsl:choose>
			<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<!-- XXXXXXXXXXXXXXXXXXXXXXX GESTIONE STYLE XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<xsl:when test="(@style and (contains(@style, 'stroke-dasharray ') or 
                             contains(@style, 'stroke-dasharray:'))) or
                (@id and substring($style-type,1,1) = 'i') or
                (@class and substring($style-type,1,1) = 'c') or
                (substring($style-type,1,1) = 'e')">
				<xsl:call-template name="ricerca-style">
					<xsl:with-param name="attributo">
						<xsl:text>stroke-dasharray</xsl:text>
					</xsl:with-param>
					<xsl:with-param name="style-type">
						<xsl:value-of select="$style-type"/>
					</xsl:with-param>
				</xsl:call-template>
			</xsl:when>
			<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<!-- XXXXXXXXXXXXXXXXXXXXXXX ATTRIBUTO STROKE-DASHARRAY XXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<xsl:when test="@stroke-dasharray">
				<xsl:attribute name="dashstyle"><xsl:value-of select="@stroke-dasharray"/></xsl:attribute>
			</xsl:when>
			<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<!-- XXXXXXXXXXXXXXXXXXXXXXX RICORSIONE XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<!-- se non trovo stroke-dasharray, lo cerco nel padre -->
			<!-- Se il padre è svg e non ha stroke-dasharray, allora: solid  -->
			<xsl:otherwise>
				<xsl:choose>
					<xsl:when test="name() = 'svg'">
						<xsl:choose>
							<xsl:when test="@stroke-dasharray">
								<xsl:attribute name="dashstyle"><xsl:value-of select="@stroke-dasharray"/></xsl:attribute>
							</xsl:when>
							<xsl:otherwise>
								<xsl:attribute name="dashstyle"><xsl:text>solid</xsl:text></xsl:attribute>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:when>
					<xsl:otherwise>
						<xsl:for-each select="..">
							<xsl:call-template name="stroke-dasharray"/>
						</xsl:for-each>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	<!-- ******************************************************************************** -->
	<!-- ***************** TEMPLATE: stroke-dashoffset ********************************** -->
	<!-- ******************************************************************************** -->
	<xsl:template name="stroke-dashoffset">
		<!-- cerca stroke-dashoffset nell'elemento, se non lo trova lo cerca nel padre e nei nodi 
     precedenti fino ad arrivare a svg, se non lo trova non lo imposta
-->
		<!-- serve per scegliere quale style applicare, se quello associato all'id, a class o 
     all'elemento, oppure nessuno dei 3 -->
		<xsl:variable name="style-type">
			<xsl:call-template name="var-style-type">
				<xsl:with-param name="attributo">
					<xsl:text>stroke-dashoffset</xsl:text>
				</xsl:with-param>
			</xsl:call-template>
		</xsl:variable>
		<xsl:choose>
			<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<!-- XXXXXXXXXXXXXXXXXXXXXXX GESTIONE STYLE XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<xsl:when test="(@style and (contains(@style, 'stroke-dashoffset ') or 
                             contains(@style, 'stroke-dashoffset:'))) or
                (@id and substring($style-type,1,1) = 'i') or
                (@class and substring($style-type,1,1) = 'c') or
                (substring($style-type,1,1) = 'e')">
				<xsl:call-template name="ricerca-style">
					<xsl:with-param name="attributo">
						<xsl:text>stroke-dashoffset</xsl:text>
					</xsl:with-param>
					<xsl:with-param name="style-type">
						<xsl:value-of select="$style-type"/>
					</xsl:with-param>
				</xsl:call-template>
			</xsl:when>
			<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<!-- XXXXXXXXXXXXXXXXXXXXXXX ATTRIBUTO STROKE-DASHOFFSET XXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<xsl:when test="@stroke-dashoffset">
				<xsl:attribute name="stroke-dashoffset"><xsl:value-of select="@stroke-dashoffset"/></xsl:attribute>
			</xsl:when>
			<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<!-- XXXXXXXXXXXXXXXXXXXXXXX RICORSIONE XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<!-- se non trovo stroke-dashoffset, lo cerco nel padre -->
			<!-- Se il padre è svg e non ha stroke-dasharray, non imposta niente  -->
			<xsl:otherwise>
				<xsl:choose>
					<xsl:when test="name() = 'svg'">
						<xsl:choose>
							<xsl:when test="@stroke-dashoffset">
								<xsl:attribute name="dashoffset"><xsl:value-of select="@stroke-dashoffset"/></xsl:attribute>
							</xsl:when>
							<xsl:otherwise>
								<!--
                        <xsl:attribute name="dashoffset">
                            <xsl:text>0</xsl:text>
                        </xsl:attribute>
                        -->
							</xsl:otherwise>
						</xsl:choose>
					</xsl:when>
					<xsl:otherwise>
						<xsl:for-each select="..">
							<xsl:call-template name="stroke-dashoffset"/>
						</xsl:for-each>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	<!-- ******************************************************************************** -->
	<!-- ***************** TEMPLATE: stroke-opacity ************************************* -->
	<!-- ******************************************************************************** -->
	<xsl:template name="stroke-opacity">
		<!-- mi chiama due funzioni ricorsive, una che mi calcola stroke-opacity e un'altra
     che mi calcola opacity, alla fine moltiplico i due valori -->
		<!-- NB: anche qui svg e vml si comportando in modo diverso: svg automaticamente per
        ogni elemento considera tutti gli attributi di opacità degli elementi ancestor
        e li moltiplica tra loro. VMl no, considera solo l'attributo di opacità 
        corrente, quindi bisogna forzare la ricerca e la moltiplicazione dei valori -->
		<xsl:variable name="opacity">
			<xsl:call-template name="opacity-ric"/>
		</xsl:variable>
		<xsl:variable name="stroke-opacity">
			<xsl:call-template name="stroke-opacity-ric"/>
		</xsl:variable>
		<xsl:value-of select="$opacity * $stroke-opacity"/>
	</xsl:template>
	<!-- ******************************************************************************** -->
	<!-- ***************** TEMPLATE: stroke-opacity-ric ********************************* -->
	<!-- ******************************************************************************** -->
	<xsl:template name="stroke-opacity-ric">
		<xsl:param name="prec">
			<xsl:text>1</xsl:text>
		</xsl:param>
		<!-- prec contiene il valore di  stroke-opacity precedentemente calcolato,
         viene moltiplicato per gli altri valori trovati nel cammino dall'elemento 
         selezionato fino alla radice
    -->
		<!-- Funzione ricorsiva che mi calcola il valore di stroke-opacity dall'elemento
      corrente fino alla radice, moltiplicando tra loro tutti i valori
      trovati -->
		<xsl:variable name="style-type">
			<xsl:call-template name="var-style-type">
				<xsl:with-param name="attributo">
					<xsl:text>stroke-opacity</xsl:text>
				</xsl:with-param>
			</xsl:call-template>
		</xsl:variable>
		<!-- valori: da 0.0 a 1.0. Di default vale 1 -->
		<xsl:choose>
			<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<!-- XXXXXXXXXXXXXXXXXXXXXXX GESTIONE STYLE XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<xsl:when test="(@style and (contains(@style, 'stroke-opacity ') or 
                    contains(@style, 'stroke-opacity:'))) or
                    (@id and substring($style-type,1,1) = 'i') or
                    (@class and substring($style-type,1,1) = 'c') or
                    (substring($style-type,1,1) = 'e')">
				<xsl:variable name="actual-opacity">
					<xsl:call-template name="ricerca-style">
						<xsl:with-param name="attributo">
							<xsl:text>stroke-opacity</xsl:text>
						</xsl:with-param>
						<xsl:with-param name="style-type">
							<xsl:value-of select="$style-type"/>
						</xsl:with-param>
					</xsl:call-template>
				</xsl:variable>
				<xsl:for-each select="..">
					<xsl:call-template name="stroke-opacity-ric">
						<xsl:with-param name="prec">
							<xsl:value-of select="$actual-opacity * $prec"/>
						</xsl:with-param>
					</xsl:call-template>
				</xsl:for-each>
			</xsl:when>
			<xsl:when test="name() = 'svg'">
				<xsl:choose>
					<xsl:when test="@stroke-opacity">
						<xsl:value-of select="@stroke-opacity  * $prec "/>
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of select="$prec "/>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:when>
			<xsl:otherwise>
				<xsl:variable name="actual-opacity">
					<xsl:choose>
						<xsl:when test="@stroke-opacity">
							<xsl:value-of select="@stroke-opacity"/>
						</xsl:when>
						<xsl:otherwise>
							<xsl:text>1</xsl:text>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:variable>
				<xsl:for-each select="..">
					<xsl:call-template name="stroke-opacity-ric">
						<xsl:with-param name="prec">
							<xsl:value-of select="$actual-opacity * $prec"/>
						</xsl:with-param>
					</xsl:call-template>
				</xsl:for-each>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	<!-- ******************************************************************************** -->
	<!-- ***************** TEMPLATE: fill *********************************************** -->
	<!-- ******************************************************************************** -->
	<xsl:template name="fill">
		<!-- cerca fill nell'elemento, se non lo trova lo cerca nel padre e nei nodi precedenti
     fino ad arrivare a svg, se non lo trova lo imposta col valore di default (black).
     Cerca anche negli elementi/attributi style
-->
		<!-- serve per scegliere quale style applicare, se quello associato all'id, a class o 
     all'elemento, oppure nessuno dei 3 -->
		<xsl:variable name="style-type">
			<xsl:call-template name="var-style-type">
				<xsl:with-param name="attributo">
					<xsl:text>fill</xsl:text>
				</xsl:with-param>
			</xsl:call-template>
		</xsl:variable>
		<xsl:choose>
			<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<!-- XXXXXXXXXXXXXXXXXXXXXXX GESTIONE STYLE XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<xsl:when test="(@style and (contains(@style, 'fill ') or contains(@style, 'fill:'))) or
                (@id and substring($style-type,1,1) = 'i') or
                (@class and substring($style-type,1,1) = 'c') or
                (substring($style-type,1,1) = 'e')">
				<xsl:call-template name="ricerca-style">
					<xsl:with-param name="attributo">
						<xsl:text>fill</xsl:text>
					</xsl:with-param>
					<xsl:with-param name="style-type">
						<xsl:value-of select="$style-type"/>
					</xsl:with-param>
				</xsl:call-template>
			</xsl:when>
			<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<!-- XXXXXXXXXXXXXXXXXXXXXXX ATTRIBUTO FILL XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<xsl:when test="@fill">
				<xsl:choose>
					<xsl:when test="substring(@fill,1,3) = 'url'">
						<xsl:variable name="nome-el">
							<xsl:value-of select="substring(@fill,6,string-length(@fill) - 6)"/>
						</xsl:variable>
						<xsl:attribute name="on"><xsl:text>true</xsl:text></xsl:attribute>
						<xsl:call-template name="gradient-pattern-ref">
							<xsl:with-param name="nome">
								<xsl:value-of select="$nome-el"/>
							</xsl:with-param>
						</xsl:call-template>
					</xsl:when>
					<xsl:when test="@fill = 'none'">
						<xsl:attribute name="on"><xsl:text>false</xsl:text></xsl:attribute>
					</xsl:when>
					<xsl:otherwise>
						<xsl:attribute name="on"><xsl:text>true</xsl:text></xsl:attribute>
						<xsl:attribute name="color"><xsl:value-of select="@fill"/></xsl:attribute>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:when>
			<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<!-- XXXXXXXXXXXXXXXXXXXXXXX RICORSIONE XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<!-- se non trovo fill, lo cerco nel padre -->
			<!-- Se il padre è svg e non ha fill, allora: black  -->
			<xsl:otherwise>
				<xsl:choose>
					<xsl:when test="name() = 'svg'">
						<xsl:choose>
							<xsl:when test="@fill">
								<xsl:attribute name="on"><xsl:text>true</xsl:text></xsl:attribute>
								<xsl:attribute name="color"><xsl:value-of select="@fill"/></xsl:attribute>
							</xsl:when>
							<xsl:otherwise>
								<!-- FILL, default: black -->
								<xsl:attribute name="on"><xsl:text>true</xsl:text></xsl:attribute>
								<xsl:attribute name="color"><xsl:text>black</xsl:text></xsl:attribute>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:when>
					<xsl:otherwise>
						<xsl:for-each select="..">
							<!-- se l'elemento è figlio di defs, e siamo arrivati a defs, lasciamo
                     non impostato l'attributo fill, potrebbe essere definito
                     nell'elemento use
                -->
							<xsl:choose>
								<xsl:when test="name() = 'defs'"/>
								<xsl:otherwise>
									<xsl:call-template name="fill"/>
								</xsl:otherwise>
							</xsl:choose>
						</xsl:for-each>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	<!-- ******************************************************************************** -->
	<!-- ***************** TEMPLATE: fill-opacity *************************************** -->
	<!-- ******************************************************************************** -->
	<xsl:template name="fill-opacity">
		<!-- mi chiama due funzioni ricorsive, una che mi calcola fill-opacity e un'altra
     che mi calcola opacity, alla fine moltiplico i due valori -->
		<xsl:variable name="opacity">
			<xsl:call-template name="opacity-ric"/>
		</xsl:variable>
		<xsl:variable name="fill-opacity">
			<xsl:call-template name="fill-opacity-ric"/>
		</xsl:variable>
		<xsl:value-of select="$opacity * $fill-opacity"/>
	</xsl:template>
	<!-- ******************************************************************************** -->
	<!-- ***************** TEMPLATE: fill-opacity-ric *********************************** -->
	<!-- ******************************************************************************** -->
	<xsl:template name="fill-opacity-ric">
		<xsl:param name="prec">
			<xsl:text>1</xsl:text>
		</xsl:param>
		<!-- prec contiene il valore di fill-opacity precedentemente calcolato,
         viene moltiplicato per gli altri valori trovati nel cammino dall'elemento 
         selezionato fino alla radice
    -->
		<!-- serve per scegliere quale style applicare, se quello associato all'id, a class o 
     all'elemento, oppure nessuno dei 3 -->
		<xsl:variable name="style-type">
			<xsl:call-template name="var-style-type">
				<xsl:with-param name="attributo">
					<xsl:text>fill-opacity</xsl:text>
				</xsl:with-param>
			</xsl:call-template>
		</xsl:variable>
		<!-- valori: da 0.0 a 1.0. Di default vale 1 -->
		<xsl:choose>
			<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<!-- XXXXXXXXXXXXXXXXXXXXXXX GESTIONE STYLE XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<xsl:when test="(@style and (contains(@style, 'fill-opacity ') or 
                    contains(@style, 'fill-opacity:'))) or
                    (@id and substring($style-type,1,1) = 'i') or
                    (@class and substring($style-type,1,1) = 'c') or
                    (substring($style-type,1,1) = 'e')">
				<xsl:variable name="actual-opacity">
					<xsl:call-template name="ricerca-style">
						<xsl:with-param name="attributo">
							<xsl:text>fill-opacity</xsl:text>
						</xsl:with-param>
						<xsl:with-param name="style-type">
							<xsl:value-of select="$style-type"/>
						</xsl:with-param>
					</xsl:call-template>
				</xsl:variable>
				<xsl:for-each select="..">
					<xsl:call-template name="fill-opacity-ric">
						<xsl:with-param name="prec">
							<xsl:value-of select="$actual-opacity * $prec"/>
						</xsl:with-param>
					</xsl:call-template>
				</xsl:for-each>
			</xsl:when>
			<xsl:when test="name() = 'svg'">
				<xsl:choose>
					<xsl:when test="@fill-opacity">
						<xsl:value-of select="@fill-opacity  * $prec "/>
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of select="$prec "/>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:when>
			<xsl:otherwise>
				<xsl:variable name="actual-opacity">
					<xsl:choose>
						<xsl:when test="@fill-opacity">
							<xsl:value-of select="@fill-opacity"/>
						</xsl:when>
						<xsl:otherwise>
							<xsl:text>1</xsl:text>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:variable>
				<xsl:for-each select="..">
					<xsl:call-template name="fill-opacity-ric">
						<xsl:with-param name="prec">
							<xsl:value-of select="$actual-opacity * $prec"/>
						</xsl:with-param>
					</xsl:call-template>
				</xsl:for-each>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	<!-- ******************************************************************************** -->
	<!-- ***************** TEMPLATE: fill-rule ****************************************** -->
	<!-- ******************************************************************************** -->
	<xsl:template name="fill-rule">
		<!-- cerca fill-rule nell'elemento, se non lo trova lo cerca nel padre e nei nodi 
     precedenti fino ad arrivare a svg, se non lo trova non lo imposta
-->
		<!-- serve per scegliere quale style applicare, se quello associato all'id, a class o 
     all'elemento, oppure nessuno dei 3 -->
		<xsl:variable name="style-type">
			<xsl:call-template name="var-style-type">
				<xsl:with-param name="attributo">
					<xsl:text>fill-rule</xsl:text>
				</xsl:with-param>
			</xsl:call-template>
		</xsl:variable>
		<xsl:choose>
			<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<!-- XXXXXXXXXXXXXXXXXXXXXXX GESTIONE STYLE XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<xsl:when test="(@style and (contains(@style, 'fill-rule ') or 
                             contains(@style, 'fill-rule:'))) or
                (@id and substring($style-type,1,1) = 'i') or
                (@class and substring($style-type,1,1) = 'c') or
                (substring($style-type,1,1) = 'e')">
				<xsl:call-template name="ricerca-style">
					<xsl:with-param name="attributo">
						<xsl:text>fill-rule</xsl:text>
					</xsl:with-param>
					<xsl:with-param name="style-type">
						<xsl:value-of select="$style-type"/>
					</xsl:with-param>
				</xsl:call-template>
			</xsl:when>
			<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<!-- XXXXXXXXXXXXXXXXXXXXXXX ATTRIBUTO FILL-RULE XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<xsl:when test="@fill-rule">
				<xsl:attribute name="fill-rule"><xsl:value-of select="@fill-rule"/></xsl:attribute>
			</xsl:when>
			<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<!-- XXXXXXXXXXXXXXXXXXXXXXX RICORSIONE XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<!-- se non trovo fill-rule, lo cerco nel padre -->
			<!-- Se il padre è svg e non ha fill-rule, non imposta niente  -->
			<xsl:otherwise>
				<xsl:choose>
					<xsl:when test="name() = 'svg'">
						<xsl:choose>
							<xsl:when test="@fill-rule">
								<xsl:attribute name="fill-rule"><xsl:value-of select="@fill-rule"/></xsl:attribute>
							</xsl:when>
							<xsl:otherwise>
								<!--
                        <xsl:attribute name="fill-rule">
                            <xsl:text>evenodd</xsl:text>
                        </xsl:attribute>
                        -->
							</xsl:otherwise>
						</xsl:choose>
					</xsl:when>
					<xsl:otherwise>
						<xsl:for-each select="..">
							<xsl:call-template name="fill-rule"/>
						</xsl:for-each>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	<!-- ******************************************************************************** -->
	<!-- ***************** TEMPLATE: opacity-ric **************************************** -->
	<!-- ******************************************************************************** -->
	<xsl:template name="opacity-ric">
		<xsl:param name="prec">
			<xsl:text>1</xsl:text>
		</xsl:param>
		<!-- prec contiene il valore di opacity precedentemente calcolato,
         viene moltiplicato per gli altri valori trovati nel cammino dall'elemento 
         selezionato fino alla radice.
    -->
		<!-- NB: anche qui svg e vml si comportando in modo diverso: svg automaticamente per
        ogni elemento considera tutti gli attributi di opacità degli elementi ancestor
        e li moltiplica tra loro. VMl no, considera solo l'attributo di opacità 
        corrente, quindi bisogna forzare la ricerca e la moltiplicazione dei valori -->
		<!-- serve per scegliere quale style applicare, se quello associato all'id, a class o 
     all'elemento, oppure nessuno dei 3 -->
		<xsl:variable name="style-type">
			<xsl:call-template name="var-style-type">
				<xsl:with-param name="attributo">
					<xsl:text>opacity</xsl:text>
				</xsl:with-param>
			</xsl:call-template>
		</xsl:variable>
		<!-- valori: da 0.0 a 1.0. Di default vale 1 -->
		<xsl:choose>
			<!-- XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<!-- XXXXXXXXXXXXXXXXXXXXXXX GESTIONE STYLE XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX -->
			<xsl:when test="(@style and ( contains(@style, ' opacity ') or 
                                  contains(@style, ' opacity:') or 
                                  contains(@style, ';opacity ') or
                                  contains(@style, ';opacity:') or 
                                  (substring(@style,1,7) = 'opacity'))) or
                    (@id and substring($style-type,1,1) = 'i') or
                    (@class and substring($style-type,1,1) = 'c') or
                    (substring($style-type,1,1) = 'e')">
				<xsl:variable name="actual-opacity">
					<xsl:call-template name="ricerca-style">
						<xsl:with-param name="attributo">
							<xsl:text>opacity</xsl:text>
						</xsl:with-param>
						<xsl:with-param name="style-type">
							<xsl:value-of select="$style-type"/>
						</xsl:with-param>
					</xsl:call-template>
				</xsl:variable>
				<xsl:for-each select="..">
					<xsl:call-template name="opacity-ric">
						<xsl:with-param name="prec">
							<xsl:value-of select="$actual-opacity * $prec"/>
						</xsl:with-param>
					</xsl:call-template>
				</xsl:for-each>
			</xsl:when>
			<xsl:when test="name() = 'svg'">
				<xsl:choose>
					<xsl:when test="@opacity">
						<xsl:value-of select="@opacity  * $prec "/>
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of select="$prec "/>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:when>
			<xsl:otherwise>
				<xsl:variable name="actual-opacity">
					<xsl:choose>
						<xsl:when test="@opacity">
							<xsl:value-of select="@opacity"/>
						</xsl:when>
						<xsl:otherwise>
							<xsl:text>1</xsl:text>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:variable>
				<xsl:for-each select="..">
					<xsl:call-template name="opacity-ric">
						<xsl:with-param name="prec">
							<xsl:value-of select="$actual-opacity * $prec"/>
						</xsl:with-param>
					</xsl:call-template>
				</xsl:for-each>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>
