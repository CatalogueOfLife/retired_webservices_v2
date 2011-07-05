<?xml version="1.0" encoding="UTF-8"?>
<application xmlns:zf="http://framework.zend.com/xml/zend-config-xml/1.0/">

	<!-- 
		WARNING: Watch out when formatting this file, because all tags
		specifying some file or path must be on a single line without spaces. 
	  -->

	<!--  P R O D U C T I O N  -->
	<production>

		<phpSettings>
			<display_startup_errors>1</display_startup_errors>
			<display_errors>1</display_errors>
			<error_reporting>1</error_reporting>
		</phpSettings>

		<includePaths>
			<library><zf:const zf:name="APPLICATION_PATH" />/../library</library>
		</includePaths>

		<bootstrap>
			<path><zf:const zf:name="APPLICATION_PATH" />/Bootstrap.php</path>
			<class>Bootstrap</class>
		</bootstrap>
		
<!-- 
		<database>
            <adapter>Mysqli</adapter>
            <params>
                <port>3306</port>
                <host>localhost</host>
                <username>root</username>
                <password>linear</password>
                <dbname>col2011ac</dbname>
                <profiler enabled="0" />
            </params>
        </database>
-->
		<resources>
			<!-- FRONT CONTROLLER -->
			<frontController>
				<moduleDirectory><zf:const zf:name="APPLICATION_PATH" />/modules</moduleDirectory>
				<defaultModule>api</defaultModule>
				<moduleControllerDirectoryName>controllers</moduleControllerDirectoryName>
				<params>
					<displayExceptions>1</displayExceptions>
				</params>
				<!--
					You need to specify the baseUrl when accessing your application
					through an Apache alias or when your virtual host's document root
					does not point to the public directory. You will also have to set
					the RewriteBase in .htaccess then.
				-->
				<baseUrl>/</baseUrl>
			</frontController>


			<!-- LAYOUT -->
			<layout>
				<layout>layout</layout>
				<layoutPath><zf:const zf:name="APPLICATION_PATH" />/layouts/scripts</layoutPath>
			</layout>

			<!-- LOGGING -->
			<log>
				<stream>
					<writerName>Firebug</writerName>
				</stream>
			</log>

			<!-- LOCALE -->
			<locale>
				<default>en_US</default>
			</locale>			
			
			<multidb>
				<application>
					<adapter>pdo_mysql</adapter>
					<host>localhost</host>
					<username>root</username>
					<password></password>
					<dbname>col_webservices</dbname>
				</application>
				<baseschema>
					<adapter>pdo_mysql</adapter>
					<host>localhost</host>
					<username>root</username>
					<password></password>
					<dbname>base_schema_v19</dbname>
				</baseschema>
			</multidb>
		</resources>

		<constants>
			<version>
				<major>0</major>
				<minor>001</minor>
			</version>
		</constants>
	</production>


	<!--  T E S T  -->
	<test zf:extends="production" />


	<!--  D E V E L O P M E N T  -->
	<development zf:extends="production">
		<phpSettings>
			<display_startup_errors>1</display_startup_errors>
			<display_errors>1</display_errors>
			<error_reporting>1</error_reporting>
		</phpSettings>
		<resources>
			<!-- FRONT CONTROLLER -->
			<frontController>
				<params>
					<displayExceptions>1</displayExceptions>
				</params>
				<!--
					You need to specify the baseUrl when accessing your application
					through an Apache alias or when your virtual host's document root
					does not point to the public directory. You will also have to set
					the RewriteBase in .htaccess then.
				-->
				<baseUrl></baseUrl>
			</frontController>
		</resources>
	</development>


</application>
