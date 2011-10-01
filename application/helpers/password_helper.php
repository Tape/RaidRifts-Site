<?php
function gen_password( $data )
{
	return sha1('raidrifts-salt-'.sha1($data));
}
?>