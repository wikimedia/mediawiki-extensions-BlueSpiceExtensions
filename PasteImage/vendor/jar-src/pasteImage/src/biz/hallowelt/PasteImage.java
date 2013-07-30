/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
package biz.hallowelt;

import java.awt.Color;
import java.awt.Graphics2D;
import java.awt.Image;
import java.awt.Toolkit;
import java.awt.datatransfer.Clipboard;
import java.awt.datatransfer.DataFlavor;
import java.awt.datatransfer.Transferable;
import java.awt.datatransfer.UnsupportedFlavorException;
import java.awt.dnd.DropTarget;
import java.awt.dnd.DropTargetDragEvent;
import java.awt.dnd.DropTargetDropEvent;
import java.awt.dnd.DropTargetEvent;
import java.awt.dnd.DropTargetListener;
import java.awt.image.BufferedImage;
import java.awt.image.ImageObserver;
import java.awt.image.RenderedImage;
import java.io.BufferedReader;
import java.io.ByteArrayOutputStream;
import java.io.File;
import java.io.IOException;
import java.io.InputStreamReader;
import java.io.OutputStream;
import java.io.OutputStreamWriter;
import java.net.URL;
import java.net.URLConnection;
import java.net.URLEncoder;
import java.security.AccessController;
import java.security.PrivilegedAction;
import java.util.List;

import javax.activation.MimetypesFileTypeMap;
import javax.imageio.ImageIO;
import javax.swing.ImageIcon;
import javax.swing.JApplet;
import javax.swing.JLabel;

import netscape.javascript.JSObject;

import org.apache.commons.codec.binary.Base64;

/**
 *
 * @author tweichart
 */
public class PasteImage extends JApplet implements DropTargetListener{

    private static final long serialVersionUID = 1L;
		static Clipboard clipboard;
	    
	    JLabel label;
	    static ByteArrayOutputStream imageBytes;
	    OutputStream outbase64 = null;
	    String [] result = null;
		private byte[] tmpImage;
		private String mimeType;


	    @Override
	    public void init() {
	        super.init();
	        // for usage when testing the applet not in browser
	        label = new JLabel ("<html><body style='margin-left: 15px;'>Drop here</body></html>");
	        label.setBackground(Color.decode("#E5EFFD"));
	        label.setOpaque(true);
	        DropTarget dt = new DropTarget(label, this);
	        add(label);
        }
	    
	    public String checkClipboard(){
	    	if (imageBytes == null){
				clipboard = (Clipboard) AccessController.doPrivileged(new PrivilegedAction<Object>() {
		            public Object run() 
		            {
		                Clipboard tempClipboard = Toolkit.getDefaultToolkit().getSystemClipboard();
		                return tempClipboard;
		            }
		        });
				try {
					PasteImage pasteMe = new PasteImage();
					byte [] resultReadImage = null;
					resultReadImage = pasteMe.pasteImageByte();
					if (resultReadImage == null){
						@SuppressWarnings({ "unchecked", "rawtypes" })
						Image erg1 = java.security.AccessController.doPrivileged(new java.security.PrivilegedAction() {
							public Object run() {
								PasteImage pasteMe = new PasteImage();
								Transferable data = Toolkit.getDefaultToolkit().getSystemClipboard().getContents(null);
								Image erg1 = pasteMe.pasteImageFile(data);
								if (erg1 == null)
									return null;
								PasteImage.imageBytes = pasteMe.convertImage(erg1);
								return erg1;
							}
						});
					}
				} catch (Throwable ta) { ta.printStackTrace (); }
			}
	    	if (PasteImage.imageBytes == null)
				return "{\"text\":\"true\"}";
	    	else
	    		return "{\"text\":\"false\"}";
	    }
	    
		public String pasteImage(){
			if (imageBytes == null){
				clipboard = (Clipboard) AccessController.doPrivileged(new PrivilegedAction<Object>() {
		            public Object run() 
		            {
		                Clipboard tempClipboard = Toolkit.getDefaultToolkit().getSystemClipboard();
		                return tempClipboard;
		            }
		        });
				try {
					PasteImage pasteMe = new PasteImage();
					byte [] resultReadImage = null;
					resultReadImage = pasteMe.pasteImageByte();
					if (resultReadImage == null){
						@SuppressWarnings({ "unchecked", "rawtypes" })
						Image erg1 = java.security.AccessController.doPrivileged(new java.security.PrivilegedAction() {
							public Object run() {
								PasteImage pasteMe = new PasteImage();
								Transferable data = Toolkit.getDefaultToolkit().getSystemClipboard().getContents(null);
								Image erg1 = pasteMe.pasteImageFile(data);
								if (erg1 == null)
									return null;
								PasteImage.imageBytes = pasteMe.convertImage(erg1);
								return erg1;
							}
						});
					}
				} catch (Throwable ta) { ta.printStackTrace (); }
			}
			
			//String out = Base64.encodeBase64URLSafeString(imageBytes.toByteArray());
			byte [] encodedBytes = null;
			if (PasteImage.imageBytes == null)
				return "{\"success\":\"false\", \"status\" : \"error\", \"code\":\"1\"}";
			try{
				encodedBytes = Base64.encodeBase64(imageBytes.toByteArray());
				
			} catch (NullPointerException e1){
				//tbd: check why nullpointer (trown if too big)
				return "{\"success\":\"false\", \"status\" : \"error\", \"code\":\"0\"}";
			}
			imageBytes = null;
			//return uploadBase64(encodedBytes);
			this.tmpImage = encodedBytes;
			return "{\"success\":\"true\"}";
	    }
		
		public String uploadBase64 (byte [] encodedBytes, String name){
			String result = "";
			try {
			    // Construct data
				String imageString = new String(encodedBytes);
			    String data = URLEncoder.encode("img", "UTF-8") + "=" + URLEncoder.encode(imageString, "UTF-8");
			    data += "&"+URLEncoder.encode("name", "UTF-8") + "=" + URLEncoder.encode(name, "UTF-8");
			    
			    // Send data			    
			    URL url = new URL(this.getCodeBase().toString() + "index.php?action=remote&mod=PasteImage&rf=pasteImageUpload");
			    //URL url = new URL("http://localhost/pasteImage/upload.php");
			    URLConnection conn = url.openConnection();
			    
			    //conn.addRequestProperty("Cookie", "ui-tabs-1=0; ui-tabs-1=0; ui-tabs-1=0; mw1170_session=d6u9i5lrgk4akghni4ppb6oii0; mw1170UserID=1; mw1170UserName=WikiSysop; bs-widget-container=null; PHPSESSID=iaqvoaic89m17tm3ej82701j15");
			    //conn.addRequestProperty("Cookie","ui-tabs-1=0; mw_1170_session=s3u8c7eeglpav9ivfbjs8epsv1; mw_1170UserID=1; mw_1170UserName=WikiSysop; PHPSESSID=s3u8c7eeglpav9ivfbjs8epsv1");
			    conn.addRequestProperty("Cookie",getCookie());
			    conn.setDoOutput(true);
			    OutputStreamWriter wr = new OutputStreamWriter(conn.getOutputStream());
			    wr.write(data);
			    wr.flush();

			    // Get the response
			    BufferedReader rd = new BufferedReader(new InputStreamReader(conn.getInputStream()));
			    String line;
			    while ((line = rd.readLine()) != null) {
			    	result += line;
			    }
			    wr.close();
			    rd.close();
			} catch (Exception e) {
				result = "{\"success\":\"false\", \"status\" : \"error\", \"code\":\"2\"}";
			}
			return result;
		}
		
		public String getCookie() {
		      /*
		      ** get all cookies for a document
		      */
		      try {
		        JSObject myBrowser = (JSObject) JSObject.getWindow(this);
		        JSObject myDocument =  (JSObject) myBrowser.getMember("document");
		        String myCookie = (String)myDocument.getMember("cookie");
		        if (myCookie.length() > 0) 
		           return myCookie;
		        }
		      catch (Exception e){
		        e.printStackTrace();
		        }
		      return "?";
		}
		public String getCookie(String name) {
		       /*
		       ** get a specific cookie by its name, parse the cookie.
		       **    not used in this Applet but can be useful
		       */
		       String myCookie = getCookie();
		       String search = name + "=";
		       if (myCookie.length() > 0) {
		          int offset = myCookie.indexOf(search);
		          if (offset != -1) {
		             offset += search.length();
		             int end = myCookie.indexOf(";", offset);
		             if (end == -1) end = myCookie.length();
		             return myCookie.substring(offset,end);
		             }
		          else 
		            System.out.println("Did not find cookie: "+name);
		          }
		        return "";
		}
	    public void drop(DropTargetDropEvent dtde)
	    {
	    	JSObject win = (JSObject) JSObject.getWindow(this);
	    	win.eval("pasteImage.showWaitMessage()");
	        int action = dtde.getDropAction();
	        dtde.acceptDrop(action);
	        fromTransferable(dtde.getTransferable());
	        dtde.dropComplete(true);
	    }
	    
	    private void fromTransferable(Transferable t)
	    {
	    	String result = "";
	    	JSObject win = (JSObject) JSObject.getWindow(this);
			Image img = pasteImageFile(t);
			if (img == null){
				result =  "{\"success\":\"false\", \"status\" : \"error\", \"code\":\"3\"}";
				win.eval("pasteImage.errorHandler("+result+")");
				return;
			}
			PasteImage.imageBytes = convertImage(img);
			if (PasteImage.imageBytes == null){
				result =  "{\"success\":\"false\", \"status\" : \"error\", \"code\":\"a\"}";
				win.eval("pasteImage.errorHandler("+result+")");
				return;
			}
			byte [] encodedBytes = null;
			
			try{
				encodedBytes = Base64.encodeBase64(imageBytes.toByteArray());
				
			} catch (NullPointerException e1){
				//tbd: check why nullpointer
				result =  "{\"success\":\"false\", \"status\" : \"error\", \"code\":\"0\"}";
				win.eval("pasteImage.errorHandler("+result+")");
				return;
			}
			
			this.tmpImage = encodedBytes;
			win.eval("pasteImage.showNameConfirm();");
	    }
	    
	    public String uploadTmpImage(String name){
	    	String result = "";
	    	result = uploadBase64(this.tmpImage, name);
			return result;
	    }

	    public byte[] pasteImageByte() {
	        Image image = getImageFromClipboard();
	        if (image == null)
	        	return null;
	        try{
	            imageBytes = getImageBytes(image, this.mimeType);
	        }
	        catch(IOException e)
	        {
	            return null;
	        }
	        if (imageBytes == null)
	        	return null;
	        else
	        	return imageBytes.toByteArray();
	    }
	    
	    public Image pasteImageFile(Transferable data) {
	    	//tbd: check stack
	    	Image erg = null;
			DataFlavor[] df = data.getTransferDataFlavors();
			for (int i = 0; i < df.length; i++) {
				if (df[i].isMimeTypeEqual(DataFlavor.imageFlavor)){
					try {
						erg = (Image) data.getTransferData(df[i]);
					} catch (UnsupportedFlavorException e) {
						erg = null;
					} catch (IOException e) {
						erg = null;
					}
				}
				else{
					if (df[i].isFlavorJavaFileListType()) {
						List fileList = null;
						try {
							fileList = (List) data.getTransferData(df[i]);
						} catch (UnsupportedFlavorException e) {
							fileList = null;
						} catch (IOException e) {
							fileList = null;
						}
						if (fileList == null)
							erg = null;
						else{
							File firstFile = (File) fileList.get(0);
							String mimeType = new MimetypesFileTypeMap().getContentType(firstFile);
							if (mimeType == "application/octet-stream")
								this.mimeType = "png";
							else if (mimeType == "image/gif")
								this.mimeType = "gif";
							else
								this.mimeType = "jpeg";
							
							erg = new ImageIcon(firstFile.getAbsolutePath()).getImage();
						}
					}
				}
			}
			return erg;
	    }


        private Image getImageFromClipboard() {
	        Transferable transferable = clipboard.getContents(null);
	        if(!transferable.isDataFlavorSupported(DataFlavor.imageFlavor))
	            return null;
	        try {
	            Image img = (Image) clipboard.getContents(null).getTransferData(DataFlavor.imageFlavor);

	            BufferedImage newImg = null;
	            int w = img.getWidth(null);
	            int h = img.getHeight(null);
	            newImg = new BufferedImage(w,h,BufferedImage.TYPE_INT_RGB);


	            ImageIcon ii = new ImageIcon(img);
	            ImageObserver is = ii.getImageObserver();

	            newImg.getGraphics().setColor(new Color(255, 255, 255));
	            newImg.getGraphics().fillRect(0, 0, w, h);
	            newImg.getGraphics().drawImage(ii.getImage(), 0, 0, is);

	            return newImg;
	        } catch (Exception e) {
	            return null;
	        }
	    }

	    private ByteArrayOutputStream getImageBytes(Image image, String format) throws IOException {
	        ByteArrayOutputStream baos = new ByteArrayOutputStream();
	        if(image instanceof RenderedImage)
	        {
	            ImageIO.write((RenderedImage)image, format, baos);
	        }

	        if(baos.size() == 0)
	            throw new IOException("No image data found");

	        return baos;
	    }
	    
	    
	    public ByteArrayOutputStream convertImage(Image img){
			final ByteArrayOutputStream baos = new ByteArrayOutputStream();
			BufferedImage bufferedImage = null;
			try{
				bufferedImage = createBufferedImageFrom(img);
			} catch (OutOfMemoryError e){
				return null;
			}
		    try {
				ImageIO.write(bufferedImage, this.mimeType, baos);
			} catch (IOException e) {
				return null;
			}
		    return baos;
		}
		
		private BufferedImage createBufferedImageFrom(final Image image) {
			  if (image instanceof BufferedImage) {
			    return (BufferedImage) image;
			  } else {
				  int RGB = 0;
				  if (this.mimeType == "png")
					  RGB = BufferedImage.TYPE_INT_ARGB;
				  else
					  RGB = BufferedImage.TYPE_INT_RGB;
			    final BufferedImage bi = new BufferedImage(image.getWidth(null), image.getHeight(null), RGB);
			    final Graphics2D g2 = bi.createGraphics();
			    g2.drawImage(image, 0, 0, null);
			    return bi;
			  }
			}

		@Override
		public void dragEnter(DropTargetDragEvent arg0) {
			// TODO Auto-generated method stub
			
		}

		@Override
		public void dragExit(DropTargetEvent arg0) {
			// TODO Auto-generated method stub
			
		}

		@Override
		public void dragOver(DropTargetDragEvent arg0) {
			// TODO Auto-generated method stub
			
		}

		@Override
		public void dropActionChanged(DropTargetDragEvent arg0) {
			// TODO Auto-generated method stub
			
		}
}
