package biz.hallowelt.InsertLink;

import java.awt.FileDialog;
import java.awt.Frame;
import javax.swing.JApplet;

public class BsFileChooserApplet extends JApplet {
	private Frame m_parent;
	private FileDialog m_fileDialog;
	private static final long serialVersionUID = -2662327150714792175L;

	public void init() {
		super.init();
	}

	public String openDialog(String title) {
		if (this.m_parent == null) {
			this.m_parent = new Frame();
		}
		if (this.m_fileDialog == null) {
			this.m_fileDialog = new FileDialog(this.m_parent, title, 0);
		}
		this.m_fileDialog.setVisible(true);
		this.m_fileDialog.toFront();
		String file = this.m_fileDialog.getFile();
		String directory = this.m_fileDialog.getDirectory();

		this.m_fileDialog.setVisible(false);
		this.m_parent.setVisible(false);
		if ((file == null) || (file.length() == 0)) {
			return "{\"success\":\"false\",\"path\":\"\"}";
		} else {
			String path = directory + file;
			path = path.replace("\\", "\\\\");
			return "{\"success\":\"true\",\"path\":\"" + path + "\"}";
		}
	}
}
