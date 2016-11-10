arp -n | grep 172.24.*.* > /var/www/html/getarp.txt
#echo 1;
#    awk -F'\t' -v fmt="{\n 'ip': '%s',\n 'mac': %'s',\n 'vendor': '%s'\n}\n" \
#        '$2 ~ /([0-9a-f][0-9a-f]:){5}/ {printf fmt,  $1, $2, $3}'
