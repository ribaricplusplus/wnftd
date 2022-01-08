import List from '@mui/material/List';
import ListItem from '@mui/material/ListItem';
import ListItemText from '@mui/material/ListItemText';

export default function HelloWorld() {
	return (
		<div className="ddlb-container">
			<List>
				<ListItem>
					<ListItemText primary="Hello!" />
				</ListItem>
				<ListItem>
					<ListItemText primary="How you doing?" />
				</ListItem>
			</List>
		</div>
	)
}
