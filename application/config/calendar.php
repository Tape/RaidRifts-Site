<?php
$config['template'] = '
{table_open}<table class="calendar" border="0" cellpadding="0" cellspacing="0">{/table_open}
{heading_row_start}<thead><tr>{/heading_row_start}

{heading_previous_cell}<th><a href="{previous_url}">&laquo;&laquo; Prev</a></th>{/heading_previous_cell}
{heading_title_cell}<th colspan="{colspan}"><h3>{heading}</h3></th>{/heading_title_cell}
{heading_next_cell}<th><a href="{next_url}">Next &raquo;&raquo;</a></th>{/heading_next_cell}

{heading_row_end}</tr></thead>{/heading_row_end}

{week_row_start}<tbody><tr>{/week_row_start}
{week_day_cell}<th>{week_day}</th>{/week_day_cell}
{week_row_end}</tr>{/week_row_end}

{cal_row_start}<tr>{/cal_row_start}
{cal_cell_start}<td>{/cal_cell_start}

{cal_cell_content}{day}{content}{/cal_cell_content}
{cal_cell_content_today}{day}{content}{/cal_cell_content_today}

{cal_cell_no_content}{day}{/cal_cell_no_content}
{cal_cell_no_content_today}<div class="highlight">{day}</div>{/cal_cell_no_content_today}

{cal_cell_blank}&nbsp;{/cal_cell_blank}

{cal_cell_end}</td>{/cal_cell_end}
{cal_row_end}</tr>{/cal_row_end}

{table_close}</tbody></table>{/table_close}
';
$config['show_next_prev'] = TRUE;
?>