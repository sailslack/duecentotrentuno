/*  ISTRUZIONI PRELIMINARI

Assumere il ruolo postgres (root dei db postgresql)
  sailslack@server: ~$ sudo su postgres

Inserire la propria password alla richiesta del sistema, 
quindi lanciare il programma di gestione psql:
  postgres@server:~$ psql -h 127.0.0.1  -U postgres -W

Creare il proprio ruolo (se non esiste già) per la creazione del db normativa,
assegnare un password (possibilmente più complessa di quelal riportata) e divenire proprietario del db:
  postgres=# CREATE ROLE sailslack WITH login password 'letmein' CREATEDB;

Da una shell diversa creare il database "normativa" vuoto (dal ruolo prprio, agente_php non lo può fare)
  sailslack@server: ~$ createdb normativa

Nella console psql assegnare i permessi al ruolo creato
  postgres=# GRANT SELECT, INSERT, UPDATE ON ALL TABLES IN SCHEMA public TO agente_php;

Chiudere psql con:
  postgres=# \q

Collegarsi al database creato con il proprio ruolo 
  sailslack@server:~$ psql normativa -h 127.0.0.1

Da psql caricare la struttura del database (questo file) con:
  normativa=>\i normativa.sql

Modificare il file config.ini in accordo con quanto fatto finora,
quindi caricare i dati nel database con i comandi:

\COPY tipologia FROM 'tipologia.csv' DELIMITER ',' CSV HEADER;
\COPY pubblicazione FROM 'pubblicazione.csv' DELIMITER ',' CSV HEADER;
\COPY legislazione FROM 'legislazione.csv' DELIMITER ',' CSV HEADER;
\COPY articoli FROM 'articoli.csv' DELIMITER ',' CSV HEADER;
\COPY rpresupposto FROM 'rpresupposto.csv' DELIMITER ',' CSV HEADER;
\COPY sanzione FROM 'sanzione.csv' DELIMITER ',' CSV HEADER;

Avendo cura di mantenere questo ordine per non avere problemi con i vincoli tra le tabelle.
I file .csv devono essere nella directory di lavoro o va specificato il percorso ai file prima del nome.

 */

/* DESCRIZIONE Tabella tipologia
codtip: codice tipo, chiave primaria
nomtip: nome esteso della tipologia (legge, gazetta, ecc.)
bretip: nome breve della tiopologia
recatt: flag record attivo
 */
CREATE TABLE public.tipologia (
       codtip serial PRIMARY KEY,
       nomtip text,
       bretip character varying(20),
       recatt boolean
);

ALTER TABLE public.tipologia OWNER TO sailslack;

COMMENT ON COLUMN public.tipologia.codtip IS 'Codice tipo';

COMMENT ON COLUMN public.tipologia.nomtip IS 'Nome esteso tipo';

COMMENT ON COLUMN public.tipologia.bretip IS 'Nome breve tipo';

COMMENT ON COLUMN public.tipologia.recatt IS 'Record attivo';


/* DESCRIZIONE Tabella pubblicazione
codpub: codice pubblicazione, chiave primaria
codpuf: aggancio al codice tipo della tabella tipologia
numpub: numero della pubblicazione
codpug: aggancio al codice tipo del supplemento
numpug: numero del supplemento
dtapub: data della pubblicazione
recatt: flag record attivo
 */
CREATE TABLE public.pubblicazione (
       codpub serial PRIMARY KEY,
       codpuf int REFERENCES tipologia(codtip),
       numpub character varying(10),
       codpug int REFERENCES tipologia(codtip),
       numpug character varying(10),
       dtapub date,
       recatt boolean
);

ALTER TABLE public.pubblicazione OWNER TO sailslack;

COMMENT ON COLUMN public.pubblicazione.codpub IS 'Codice pubblicazione';

COMMENT ON COLUMN public.pubblicazione.codpuf IS 'Codice tipo pubblicazione';

COMMENT ON COLUMN public.pubblicazione.numpub IS 'Numero pubblicazione';

COMMENT ON COLUMN public.pubblicazione.codpug IS 'Codice supplemento';

COMMENT ON COLUMN public.pubblicazione.numpug IS 'Numero supplemento';

COMMENT ON COLUMN public.pubblicazione.dtapub IS 'Data pubblicazione';

COMMENT ON COLUMN public.pubblicazione.recatt IS 'Record attivo';


/* DESCRIZIONE Tabella legislazione
codleg: codice legge, chiave primaria
codtip: aggancio al tipo di legge
dtaleg: data della legge
numleg: numero della legge
titleg: titolo della legge
codpub: aggancio alla pubblicazione della legge
vigleg: data di entrata in vigore
codart: aggancio al codice articolo di variazione  (= abrogato)
modleg: aggancio al codice legge di modifica/ratifica
recatt: flag record attivo
 */
CREATE TABLE public.legislazione (
       codleg serial PRIMARY KEY,
       codtip int REFERENCES tipologia,
       dtaleg date,
       numleg character varying(10),
       titleg text,
       codpub int REFERENCES pubblicazione,
       vigleg date,
       codart int,
       modleg int REFERENCES legislazione(codleg),
       recatt boolean
);

ALTER TABLE public.legislazione OWNER TO sailslack;

COMMENT ON COLUMN public.legislazione.codleg IS 'Codice legge';

COMMENT ON COLUMN public.legislazione.codtip IS 'Tipo di legge';

COMMENT ON COLUMN public.legislazione.dtaleg IS 'Data della legge';

COMMENT ON COLUMN public.legislazione.numleg IS 'Numero della legge';

COMMENT ON COLUMN public.legislazione.titleg IS 'Titolo della legge';

COMMENT ON COLUMN public.legislazione.codpub IS 'Pubblicazione';

COMMENT ON COLUMN public.legislazione.vigleg IS 'Entrata in vigore';

COMMENT ON COLUMN public.legislazione.codart IS 'Abrogazione';

COMMENT ON COLUMN public.legislazione.modleg IS 'Ratifica o Rettifica';

COMMENT ON COLUMN public.legislazione.recatt IS 'Record attivo';


/* DESCRIZIONE Tabella articoli
codart: codice articolo, chiave primaria
codleg: codice legge cui appartiene
numart: numero articolo
comart: comma articolo (0 x rubrica)
letart: lettera articolo (0 x alinea)
txtart: testo dell'articolo
codarv: articolo di modifica, il codart che ha introdotto un comma in una legge esistente
codarx: articolo di abrogazione, il codart ha sostituito o abrogato il presente codart.
recatt: flag record attivo
 */
CREATE TABLE public.articoli (
       codart serial PRIMARY KEY,
       codleg int REFERENCES legislazione,
       numart character varying(25),
       comart character varying(25),
       letart character varying(25),
       txtart text,
       codarv int REFERENCES articoli(codart),
       codarx int REFERENCES articoli(codart),
       recatt boolean
);

ALTER TABLE public.articoli OWNER TO sailslack;

ALTER TABLE legislazione ADD FOREIGN KEY (codart) REFERENCES articoli(codart);

COMMENT ON COLUMN public.articoli.codart IS 'Codice articolo';

COMMENT ON COLUMN public.articoli.codleg IS 'Legge cui appartiene';

COMMENT ON COLUMN public.articoli.numart IS 'Articolo numero';

COMMENT ON COLUMN public.articoli.comart IS 'Comma numero';

COMMENT ON COLUMN public.articoli.letart IS 'Lettera';

COMMENT ON COLUMN public.articoli.txtart IS 'Testo';

COMMENT ON COLUMN public.articoli.codarv IS 'Articolo di modifica';

COMMENT ON COLUMN public.articoli.codarx IS 'Articolo di abrogazione';

COMMENT ON COLUMN public.articoli.recatt IS 'Record attivo';



/* DESCRIZIONE Tabella rpresupposto
codrpp: codice reato presupposto, chiave primaria
artdec: numero articolo 231. Da questo si può trovare 
            numart + comart=0 descrizione del reato
            join con articoli.codleg x vigleg = data di introduzione dell'articolo
artrpr: riferimento a articoli(codart) per l'articolo del reato:
            da codleg con join per fonte normativa
            numart = numero articolo
            comart = comma articolo
            letart = lettera articolo
            txtart = testo dell'articolo originale
codleg: riferimento alla legge originale, serve per trovare con esattezza il codart corrispondente 
codvar: riferimento a tipologia(codtip) per la variante (gravità, tenuità, ecc.)
recatt: flag record attivo
 */
CREATE TABLE public.rpresupposto (
       codrpp serial PRIMARY KEY,
       artdec character varying(25),
       comdec character varying(25),
       letdec character varying(25),
       artleg character varying(25),
       comleg character varying(25),
       letleg character varying(25),
       codleg int REFERENCES legislazione(codleg),
       codvar int REFERENCES tipologia(codtip),
       recatt boolean
);

ALTER TABLE public.rpresupposto OWNER TO sailslack;

COMMENT ON COLUMN public.rpresupposto.codrpp IS 'Codice reato presupposto';

COMMENT ON COLUMN public.rpresupposto.artdec IS 'Articolo Dlgs 231';

COMMENT ON COLUMN public.rpresupposto.comdec IS 'Comma Articolo Dlgs 231';

COMMENT ON COLUMN public.rpresupposto.letdec IS 'Lettera Articolo Dlgs 231';

COMMENT ON COLUMN public.rpresupposto.artleg IS 'Articolo origine reato';

COMMENT ON COLUMN public.rpresupposto.comleg IS 'Comma articolo origine reato';

COMMENT ON COLUMN public.rpresupposto.letleg IS 'Lettera articolo origine reato';

COMMENT ON COLUMN public.rpresupposto.codleg IS 'Fonte normativa originale';

COMMENT ON COLUMN public.rpresupposto.codvar IS 'Variante di reato';

COMMENT ON COLUMN public.rpresupposto.recatt IS 'Record attivo';




/* DESCRIZIONE Tabella sanzione
codsan codice sanzione, chiave primaria
codrpp riferimento a rpresupposto = aggancia la sanzioneal reato (1 -> 1000)
codart riferimento a articoli per la tipologia di sanzione:
            art 9 comma 1 let a x pecuniaria
            art 9 comma 2 let a, b, c, d, e x interdittiva (anche più di una)
            art 9 comma 1 let c x confisca
            art 9 comma 1 let d x pubblicazione
valmin valore minimo della sanzione
valmax valore massimo della sanzione
recatt: flag record attivo
 */
CREATE TABLE public.sanzione (
       codsan serial PRIMARY KEY,
       codrpp int REFERENCES rpresupposto,
       codart int REFERENCES articoli,
       valmin int,
       valmax int,
       recatt boolean
);

ALTER TABLE public.sanzione OWNER TO sailslack;

COMMENT ON COLUMN public.sanzione.codsan IS 'Codice reato presupposto';

COMMENT ON COLUMN public.sanzione.codrpp IS 'Reato presupposto';

COMMENT ON COLUMN public.sanzione.codart IS 'Articolo sanzione Dlgs 231';

COMMENT ON COLUMN public.sanzione.valmin IS 'Minimo della sanzione';

COMMENT ON COLUMN public.sanzione.valmax IS 'Massimo della sanzione';

COMMENT ON COLUMN public.sanzione.recatt IS 'Record attivo';
