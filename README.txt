- Cloning terlebih dahulu repository git nya 

- Pindahkan file Project SeedDMS ke /var/www/html/

- Lalukan konfigurasi pada seeddms di seeddms/conf/setting.xaml
  Silahkan ubah konfigurasi sesuai dengan lokasi direktori dan credential Database.

  Lokasi Direktori:
	rootDir="/var/www/html/seeddms/" 
	httpRoot="/seeddms/" 
	contentDir="/var/www/html/seeddms/data/" 
	stagingDir="/var/www/html/seeddms/data/staging/" 
	luceneDir="/var/www/html/seeddms/data/lucene/" 
	cacheDir="/var/www/html/seeddms/data/cache/" 
	dropFolderDir="" 
	backupDir="/var/www/html/seeddms/data/backup/" checkOutDir="" 
	extraPath="/var/www/html/seeddms/pear/"

  Credential DB:
  	dbDriver="mysql" 
	dbHostname="localhost" 
	dbDatabase="dms" 
	dbUser="seeddms" 
	dbPass="seeddms123" 
	doNotCheckVersion="false"

- Setelah selesai ubah konfigurasi, silahkan buat file dan diberi nama ENABLE_INSTALL_TOOL di seeddms/conf/. Untuk isinya di kosongkan saja. Dan berikan kepemikikan secara rekursif kepada direktori seeddms sebagai saya menggunakan apache contoh: 
	chown -R apache:apache seeddms/

- jalankan project di browser dan lakukan proses installasi. Sebagai contoh: http://localhost/seeddms/install/install.php . Pada proses tersebut kita akan melakukan Generate table secara otomatis. 

- Apabila proses installasi selesai silahkan lakukan Login terlebih dahulu di http://localhost/seeddms/

- Jika terdapat kendala silahkan lakukan perintah tsb di terminal:
	# chcon -R --type httpd_sys_rw_content_t /var/www/html/seeddms/data
	# chcon -R --type httpd_sys_rw_content_t /var/www/html/seeddms/conf
