#!/usr/bin/env bash
#
# Read-only inventory of the server, run before anything is installed.
#
# Establishes the starting point. Nothing in this file writes, installs,
# enables, disables, or restarts anything. Every command is a read. That is the
# whole contract, and it is why this is a script rather than a list of commands
# to paste: a script can be read once and audited, and it cannot drift between
# the version that was reviewed and the version that ran.
#
#   # ON SERVER
#   sudo bash scripts/inventory.sh
#
# sudo is only for the two checks that need it (listening sockets with process
# names, and sshd's effective config). It runs fine without sudo, with those two
# sections reduced.
#
# Paste the whole output back before provisioning begins.

set -uo pipefail   # deliberately no -e: a missing tool should skip a section,
                   # not abandon the inventory.

hr()  { printf '\n\033[1m--- %s %s\033[0m\n' "$*" "$(printf '%.0s-' $(seq 1 $((60 - ${#1}))))"; }
have() { command -v "$1" >/dev/null 2>&1; }

echo "Everything4Cats server inventory — $(date -u '+%Y-%m-%d %H:%M UTC')"

hr "Host, size, and resources"
# The shell only knows what the kernel was told. Instance plan, region and cost
# are console answers and are listed at the bottom under what cannot be answered
# from here.
hostnamectl 2>/dev/null || uname -a
echo
echo "CPU:    $(nproc) vCPU"
free -h
echo
df -h /
# There was a metadata-service lookup here for region and instance size. It
# addressed a provider-specific endpoint path, and against a different provider
# it returns nothing and prints blank fields rather than erroring, which reads
# as an answer. Those two facts come from the console instead, at the bottom of
# this file, where they cannot be silently empty.

hr "Ubuntu version"
# The container is 24.04. A mismatch means parts of what the Dockerfile codified
# do not transfer as written, above all the php-fpm conf name.
lsb_release -a 2>/dev/null || cat /etc/os-release

hr "What the image already installed (the load-bearing question)"
# A plain Ubuntu image should report every one of these as absent. Anything
# present means a marketplace image, which would arrive with the whole stack
# already configured and skip what this build was structured around.
for c in apache2 nginx php php-fpm mysql mariadb wp docker certbot; do
	if have "$c"; then
		printf '  PRESENT  %-10s %s\n' "$c" "$(command -v "$c")"
	else
		printf '  absent   %s\n' "$c"
	fi
done
echo
echo "WordPress on disk:"
find /var/www /srv /usr/share -maxdepth 4 -name wp-includes -type d 2>/dev/null | head || true
[ -d /var/www ] && { echo; echo "/var/www:"; ls -la /var/www 2>/dev/null; }

hr "Web server packages and services"
dpkg -l 2>/dev/null | grep -iE '^ii\s+(apache2|nginx|php|mysql-server|mariadb-server)' | awk '{printf "  %-34s %s\n", $2, $3}' || echo "  none"
echo
systemctl list-units --type=service --state=running --no-pager --no-legend 2>/dev/null \
	| grep -iE 'apache|nginx|php|mysql|maria' || echo "  no web or database services running"

hr "Listening on 80 and 443"
if have ss; then
	ss -tlnp 2>/dev/null | awk 'NR==1 || /:80 |:443 /'
	echo
	echo "All listening TCP:"
	ss -tlnp 2>/dev/null
else
	netstat -tlnp 2>/dev/null
fi

hr "SSH as delivered"
# Effective config, not the file: sshd -T resolves Include directives, which is
# where Ubuntu 24.04 puts most of the interesting defaults.
if have sshd; then
	sshd -T 2>/dev/null | grep -iE '^(port|permitrootlogin|passwordauthentication|pubkeyauthentication|permitemptypasswords)' \
		|| echo "  (needs root to read effective config)"
else
	grep -iE '^\s*(Port|PermitRootLogin|PasswordAuthentication|PubkeyAuthentication)' \
		/etc/ssh/sshd_config /etc/ssh/sshd_config.d/*.conf 2>/dev/null
fi

hr "Firewall (host level)"
# ufw on the instance and the Lightsail firewall in the console are two separate
# things, and either can block without the other saying so. This only sees ufw.
if have ufw; then ufw status verbose 2>/dev/null || echo "  (needs root)"; else echo "  ufw not installed"; fi
echo
if have iptables; then
	echo "iptables INPUT policy: $(iptables -S INPUT 2>/dev/null | head -1 || echo '(needs root)')"
fi
if have fail2ban-client; then
	echo "fail2ban: $(fail2ban-client status 2>/dev/null | head -3 | tr '\n' ' ' || echo 'installed, not running')"
else
	echo "fail2ban: not installed"
fi

hr "Unattended upgrades"
if [ -f /etc/apt/apt.conf.d/20auto-upgrades ]; then cat /etc/apt/apt.conf.d/20auto-upgrades; else echo "  not configured"; fi

hr "Swap"
# Ubuntu cloud images ship without swap, and MySQL plus php-fpm on a small
# instance will OOM under a build without it. provision.sh creates 2 GiB.
swapon --show 2>/dev/null || echo "  no swap configured"

hr "What else is already on this host"
# On a fresh instance this should come back close to empty, and that is the
# point: it is the baseline every later change is read against. On a re-run
# months from now it answers the more important question, which is what is
# already here that must not be disturbed.
echo "Enabled vhosts:"
ls -1 /etc/apache2/sites-enabled/ 2>/dev/null || echo "  (no apache2)"
ls -1 /etc/nginx/sites-enabled/ 2>/dev/null || echo "  (no nginx)"
echo
echo "Document roots in use:"
grep -rhoP '(DocumentRoot|root)\s+\K\S+' /etc/apache2/sites-enabled/ /etc/nginx/sites-enabled/ 2>/dev/null | sort -u || true
echo
echo "Databases present:"
mysql -N -B -e 'SHOW DATABASES;' 2>/dev/null || echo "  (cannot read; needs root or no MySQL)"
echo
echo "Existing TLS certificates:"
certbot certificates 2>/dev/null | grep -E 'Certificate Name|Domains|Expiry' || echo "  (none, or certbot absent)"
echo
echo "Docker:"
if have docker; then
	docker --version
	docker ps --format '  {{.Names}}  {{.Image}}  {{.Status}}' 2>/dev/null || echo "  (cannot list; needs group membership)"
else
	echo "  not installed"
fi

hr "Cannot be answered from the shell"
cat <<'EOF'
  Check these in the Lightsail console and record them by hand:

    - Instance plan, region, and monthly cost. The cost is the burn rate against
      the account credits, so it decides when the runway ends. Record the number
      read from the console rather than an estimate.
    - Static IP: whether one is attached. Without it the address is released on
      stop/start, which breaks DNS and TLS renewal later rather than now.
    - Firewall rules: which ports the console allows. Separate from ufw above,
      and either can block without the other saying so.
    - Automatic snapshots: enabled or not, and the schedule. Nothing is backed
      up unless it is turned on.
    - Whether outbound SMTP on port 25 is blocked. It is, by default. This is
      why mail needs a relay over 587/465 or an HTTP API.
EOF

echo
echo "Inventory complete. Nothing was changed."
